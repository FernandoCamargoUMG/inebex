<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class IncidentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Incident::with(['user', 'assignedUser']);
            
            // Filtrar por usuario si se proporciona
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }
            
            // Filtrar por estado
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            
            // Filtrar por prioridad
            if ($request->has('priority')) {
                $query->where('priority', $request->priority);
            }
            
            $incidents = $query->orderBy('created_at', 'desc')->get();
            
            return response()->json([
                'success' => true,
                'data' => $incidents,
                'message' => 'Incidencias recuperadas exitosamente'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar incidencias: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'assigned_user_id' => 'nullable|exists:users,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'priority' => 'in:low,medium,high,critical',
                'status' => 'in:open,in_progress,resolved,closed',
                'due_date' => 'nullable|date|after:now'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $incident = Incident::create($request->all());
            $incident->load(['user', 'assignedUser']);

            return response()->json([
                'success' => true,
                'data' => $incident,
                'message' => 'Incidencia creada exitosamente'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear incidencia: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $incident = Incident::with(['user', 'assignedUser'])->find($id);
            
            if (!$incident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incidencia no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $incident,
                'message' => 'Incidencia recuperada exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar incidencia: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $incident = Incident::find($id);
            
            if (!$incident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incidencia no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'assigned_user_id' => 'nullable|exists:users,id',
                'title' => 'sometimes|string|max:255',
                'description' => 'sometimes|string',
                'priority' => 'sometimes|in:low,medium,high,critical',
                'status' => 'sometimes|in:open,in_progress,resolved,closed',
                'due_date' => 'nullable|date',
                'resolution' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Si se marca como resuelto/cerrado, agregar fecha de resolución
            if (in_array($request->status, ['resolved', 'closed']) && !$incident->resolved_at) {
                $request->merge(['resolved_at' => now()]);
            }

            $incident->update($request->all());
            $incident->load(['user', 'assignedUser']);

            return response()->json([
                'success' => true,
                'data' => $incident,
                'message' => 'Incidencia actualizada exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar incidencia: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $incident = Incident::find($id);
            
            if (!$incident) {
                return response()->json([
                    'success' => false,
                    'message' => 'Incidencia no encontrada'
                ], Response::HTTP_NOT_FOUND);
            }

            $incident->delete();

            return response()->json([
                'success' => true,
                'message' => 'Incidencia eliminada exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar incidencia: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get incidents by priority
     */
    public function byPriority(string $priority)
    {
        try {
            $incidents = Incident::with(['user', 'assignedUser'])
                                ->where('priority', $priority)
                                ->orderBy('created_at', 'desc')
                                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $incidents,
                'message' => "Incidencias con prioridad '{$priority}' recuperadas exitosamente"
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar incidencias: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get overdue incidents
     */
    public function overdue()
    {
        try {
            $incidents = Incident::with(['user', 'assignedUser'])
                                ->where('due_date', '<', now())
                                ->whereNotIn('status', ['resolved', 'closed'])
                                ->orderBy('due_date', 'asc')
                                ->get();
            
            return response()->json([
                'success' => true,
                'data' => $incidents,
                'message' => 'Incidencias vencidas recuperadas exitosamente'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar incidencias vencidas: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}