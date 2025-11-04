<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AppointmentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AppointmentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $appointmentTypes = AppointmentType::all();
            
            return response()->json([
                'success' => true,
                'data' => $appointmentTypes,
                'message' => 'Tipos de citas recuperados exitosamente'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar tipos de citas: ' . $e->getMessage()
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
                'name' => 'required|string|max:255|unique:appointment_types',
                'description' => 'nullable|string|max:500',
                'duration_minutes' => 'nullable|integer|min:15|max:480',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $appointmentType = AppointmentType::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $appointmentType,
                'message' => 'Tipo de cita creado exitosamente'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de cita: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $appointmentType = AppointmentType::find($id);
            
            if (!$appointmentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de cita no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $appointmentType,
                'message' => 'Tipo de cita recuperado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar tipo de cita: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $appointmentType = AppointmentType::find($id);
            
            if (!$appointmentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de cita no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255|unique:appointment_types,name,' . $id,
                'description' => 'nullable|string|max:500',
                'duration_minutes' => 'nullable|integer|min:15|max:480',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $appointmentType->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $appointmentType,
                'message' => 'Tipo de cita actualizado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de cita: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $appointmentType = AppointmentType::find($id);
            
            if (!$appointmentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de cita no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar si hay citas con este tipo
            if ($appointmentType->appointments()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el tipo de cita porque está siendo usado por citas existentes'
                ], Response::HTTP_CONFLICT);
            }

            $appointmentType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de cita eliminado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de cita: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}