<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Document::with(['record', 'documentType']);
            
            // Filtrar por expediente si se proporciona
            if ($request->has('record_id')) {
                $query->where('record_id', $request->record_id);
            }
            
            $documents = $query->get();
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Documentos recuperados exitosamente'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar documentos: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage (Upload PDF).
     */
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'record_id' => 'required|exists:records,id',
                'document_type_id' => 'required|exists:document_types,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:500',
                'file' => 'required|file|mimes:pdf|max:10240' // Máximo 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            // Procesar el archivo
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $fileName = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.pdf';
            
            // Crear directorio si no existe
            $directory = 'expedientes/' . $request->record_id;
            
            // Guardar archivo en storage/app/public/expedientes/{record_id}/
            $filePath = $file->storeAs($directory, $fileName, 'public');
            
            // Crear registro en base de datos
            $document = Document::create([
                'record_id' => $request->record_id,
                'document_type_id' => $request->document_type_id,
                'title' => $request->title,
                'description' => $request->description,
                'file_name' => $originalName,
                'file_path' => $filePath,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType()
            ]);

            $document->load(['record', 'documentType']);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Documento subido exitosamente'
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $document = Document::with(['record', 'documentType'])->find($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Documento recuperado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Download the specified document.
     */
    public function download(string $id)
    {
        try {
            $document = Document::find($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $filePath = storage_path('app/public/' . $document->file_path);
            
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado en el sistema'
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->download($filePath, $document->file_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $document = Document::find($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:500',
                'document_type_id' => 'sometimes|exists:document_types,id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $document->update($request->only(['title', 'description', 'document_type_id']));
            $document->load(['record', 'documentType']);

            return response()->json([
                'success' => true,
                'data' => $document,
                'message' => 'Documento actualizado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $document = Document::find($id);
            
            if (!$document) {
                return response()->json([
                    'success' => false,
                    'message' => 'Documento no encontrado'
                ], Response::HTTP_NOT_FOUND);
            }

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }

            // Eliminar registro de base de datos
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Documento eliminado exitosamente'
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar documento: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get documents by record ID
     */
    public function byRecord(string $recordId)
    {
        try {
            $documents = Document::with(['documentType'])
                               ->where('record_id', $recordId)
                               ->get();
            
            return response()->json([
                'success' => true,
                'data' => $documents,
                'message' => 'Documentos del expediente recuperados exitosamente'
            ], Response::HTTP_OK);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al recuperar documentos: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}