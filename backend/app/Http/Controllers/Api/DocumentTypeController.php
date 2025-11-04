<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class DocumentTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $documentTypes = DocumentType::all();
            
            return response()->json([
                'success' => true,
                'data' => $documentTypes,
                'message' => 'Tipos de documentos recuperados exitosamente'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar tipos de documentos: ' . $e->getMessage()
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
                'name' => 'required|string|max:255|unique:document_types',
                'description' => 'nullable|string|max:500',
                'is_required' => 'boolean',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $documentType = DocumentType::create($request->all());

            return response()->json([
                'success' => true,
                'data' => $documentType,
                'message' => 'Tipo de documento creado exitosamente'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear tipo de documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $documentType = DocumentType::find($id);
            
            if (!$documentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $documentType,
                'message' => 'Tipo de documento recuperado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar tipo de documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $documentType = DocumentType::find($id);
            
            if (!$documentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255|unique:document_types,name,' . $id,
                'description' => 'nullable|string|max:500',
                'is_required' => 'boolean',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $documentType->update($request->all());

            return response()->json([
                'success' => true,
                'data' => $documentType,
                'message' => 'Tipo de documento actualizado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar tipo de documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $documentType = DocumentType::find($id);
            
            if (!$documentType) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tipo de documento no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Verificar si hay documentos con este tipo
            if ($documentType->documents()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el tipo de documento porque está siendo usado por documentos existentes'
                ], Response::HTTP_CONFLICT);
            }

            $documentType->delete();

            return response()->json([
                'success' => true,
                'message' => 'Tipo de documento eliminado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar tipo de documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}