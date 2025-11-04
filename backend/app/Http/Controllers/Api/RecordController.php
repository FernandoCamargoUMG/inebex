<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Record;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class RecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $records = Record::with(['user', 'profile'])->get();
            
            return response()->json([
                'success' => true,
                'data' => $records,
                'message' => 'Expedientes recuperados exitosamente'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar expedientes: ' . $e->getMessage()
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
                'profile_id' => 'required|exists:profiles,id',
                'record_number' => 'required|string|max:50|unique:records',
                'status' => 'in:active,inactive,completed,cancelled',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $record = Record::create($request->all());
            $record->load(['user', 'profile']);

            return response()->json([
                'success' => true,
                'data' => $record,
                'message' => 'Expediente creado exitosamente'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear expediente: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $record = Record::with(['user', 'profile', 'documents'])->find($id);
            
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expediente no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $record,
                'message' => 'Expediente recuperado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar expediente: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $record = Record::find($id);
            
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expediente no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'user_id' => 'sometimes|exists:users,id',
                'profile_id' => 'sometimes|exists:profiles,id',
                'record_number' => 'sometimes|string|max:50|unique:records,record_number,' . $id,
                'status' => 'sometimes|in:active,inactive,completed,cancelled',
                'notes' => 'nullable|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $record->update($request->all());
            $record->load(['user', 'profile']);

            return response()->json([
                'success' => true,
                'data' => $record,
                'message' => 'Expediente actualizado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar expediente: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $record = Record::find($id);
            
            if (!$record) {
                return response()->json([
                    'success' => false,
                    'message' => 'Expediente no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar si hay documentos asociados
            if ($record->documents()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el expediente porque tiene documentos asociados'
                ], Response::HTTP_CONFLICT);
            }

            $record->delete();

            return response()->json([
                'success' => true,
                'message' => 'Expediente eliminado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar expediente: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get records by status
     */
    public function byStatus(string $status)
    {
        try {
            $records = Record::with(['user', 'profile'])
                            ->where('status', $status)
                            ->get();
            
            return response()->json([
                'success' => true,
                'data' => $records,
                'message' => "Expedientes con estado '{$status}' recuperados exitosamente"
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar expedientes: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}