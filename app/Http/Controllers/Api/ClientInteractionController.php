<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ClientInteraction;
use App\Models\ClientInteractionAttachment;
use App\Models\Client;

class ClientInteractionController extends Controller
{
    /**
     * Display a listing of client interactions.
     */
    public function index(Request $request, $clientId)
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            $query = ClientInteraction::with([
                'user:id,name,email',
                'attachments'
            ])
            ->where('client_id', $clientId);

            // Filtros
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            if ($request->has('date_from')) {
                $query->where('interaction_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('interaction_date', '<=', $request->date_to);
            }

            // Ordenamiento por fecha descendente
            $query->orderBy('interaction_date', 'desc');

            $perPage = $request->get('per_page', 15);
            $interactions = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $interactions,
                'message' => 'Interacciones obtenidas exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las interacciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created interaction.
     */
    public function store(Request $request, $clientId)
    {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:meeting,call,whatsapp,email,other',
                'description' => 'required|string|max:1000',
                'interaction_date' => 'required|date|before_or_equal:now',
                'archivos.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp|max:10240' // Máx. 10MB
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear la interacción
            $interaction = new ClientInteraction();
            $interaction->client_id = $clientId;
            $interaction->user_id = Auth::id();
            $interaction->type = $request->type;
            $interaction->description = $request->description;
            $interaction->interaction_date = $request->interaction_date;
            $interaction->save();

            // Procesar archivos adjuntos si existen
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $index => $file) {
                    if ($file->isValid()) {
                        $this->processFileUpload($file, $interaction->id, $index);
                    }
                }
            }

            // Cargar relaciones para respuesta
            $interaction->load(['user:id,name,email', 'attachments']);

            return response()->json([
                'success' => true,
                'data' => $interaction,
                'message' => 'Interacción creada exitosamente'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la interacción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified interaction.
     */
    public function show($clientId, $interactionId)
    {
        try {
            $interaction = ClientInteraction::with([
                'client:client_id,name',
                'user:id,name,email',
                'attachments'
            ])
            ->where('client_id', $clientId)
            ->find($interactionId);

            if (!$interaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interacción no encontrada'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $interaction,
                'message' => 'Interacción obtenida exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la interacción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified interaction.
     */
    public function update(Request $request, $clientId, $interactionId)
    {
        try {
            $interaction = ClientInteraction::where('client_id', $clientId)->find($interactionId);

            if (!$interaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interacción no encontrada'
                ], 404);
            }

            // Validación de los datos
            $validator = Validator::make($request->all(), [
                'type' => 'sometimes|required|in:meeting,call,whatsapp,email,other',
                'description' => 'sometimes|required|string|max:1000',
                'interaction_date' => 'sometimes|required|date|before_or_equal:now',
                'archivos.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar la interacción
            $interaction->update($validator->validated());

            // Procesar archivos adjuntos si existen
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $index => $file) {
                    if ($file->isValid()) {
                        $this->processFileUpload($file, $interaction->id, $index);
                    }
                }
            }

            // Cargar relaciones para respuesta
            $interaction->load(['user:id,name,email', 'attachments']);

            return response()->json([
                'success' => true,
                'data' => $interaction,
                'message' => 'Interacción actualizada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la interacción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified interaction.
     */
    public function destroy($clientId, $interactionId)
    {
        try {
            $interaction = ClientInteraction::where('client_id', $clientId)->find($interactionId);

            if (!$interaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interacción no encontrada'
                ], 404);
            }

            // Eliminar archivos adjuntos físicamente
            foreach ($interaction->attachments as $attachment) {
                if (Storage::disk('public')->exists($attachment->file_path)) {
                    Storage::disk('public')->delete($attachment->file_path);
                }
                $attachment->delete();
            }

            $interaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Interacción eliminada exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la interacción: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Attach files to existing interaction.
     */
    public function attachFiles(Request $request, $clientId, $interactionId)
    {
        try {
            $interaction = ClientInteraction::where('client_id', $clientId)->find($interactionId);

            if (!$interaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Interacción no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'archivos.*' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,webp|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $attachments = [];

            // Procesar archivos adjuntos
            if ($request->hasFile('archivos')) {
                foreach ($request->file('archivos') as $index => $file) {
                    if ($file->isValid()) {
                        $attachment = $this->processFileUpload($file, $interactionId, $index);
                        $attachments[] = $attachment;
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $attachments,
                'message' => count($attachments) > 0
                    ? 'Archivos adjuntos exitosamente'
                    : 'No se adjuntaron archivos'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al adjuntar archivos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove attachment from interaction.
     */
    public function removeAttachment($clientId, $interactionId, $attachmentId)
    {
        try {
            $attachment = ClientInteractionAttachment::where('client_interaction_id', $interactionId)
                ->where('id', $attachmentId)
                ->first();

            if (!$attachment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo adjunto no encontrado'
                ], 404);
            }

            // Eliminar archivo físico
            if (Storage::disk('public')->exists($attachment->file_path)) {
                Storage::disk('public')->delete($attachment->file_path);
            }

            $attachment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Archivo adjunto eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar archivo adjunto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download attachment.
     */
    public function downloadAttachment($clientId, $interactionId, $attachmentId)
    {
        try {
            $attachment = ClientInteractionAttachment::where('client_interaction_id', $interactionId)
                ->where('id', $attachmentId)
                ->first();

            if (!$attachment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo adjunto no encontrado'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $attachment->file_path);

            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado en el servidor'
                ], 404);
            }

            return response()->download($filePath, $attachment->original_name);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar archivo adjunto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper method to process file upload.
     */
    private function processFileUpload($file, $interactionId, $index)
    {
        $mimeType = $file->getMimeType();

        // Crear nombre único para el archivo
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $storedName = 'client_interaction_' . $interactionId . '_' . time() . '_' . $index . '.' . $extension;

        // Guardar archivo en storage
        $path = $file->storeAs('client_interactions/' . $interactionId, $storedName, 'public');

        // Crear registro de archivo adjunto
        $attachment = new ClientInteractionAttachment();
        $attachment->client_interaction_id = $interactionId;
        $attachment->original_name = $originalName;
        $attachment->stored_name = $storedName;
        $attachment->file_path = $path;
        $attachment->mime_type = $mimeType;
        $attachment->file_size = $file->getSize();
        $attachment->file_type = $this->getFileTypeFromMimeType($mimeType);
        $attachment->uploaded_by = Auth::id();
        $attachment->save();

        $attachment->load('uploadedBy');

        return $attachment;
    }

    /**
     * Helper method to get file type from mime type.
     */
    private function getFileTypeFromMimeType($mimeType)
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'imagen';
        } elseif ($mimeType === 'application/pdf') {
            return 'documento';
        } elseif (str_contains($mimeType, 'word')) {
            return 'documento';
        } elseif (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
            return 'hoja_calculo';
        } elseif (str_contains($mimeType, 'powerpoint') || str_contains($mimeType, 'presentation')) {
            return 'presentacion';
        } elseif (str_contains($mimeType, 'archive') || str_contains($mimeType, 'zip') || str_contains($mimeType, 'compressed')) {
            return 'archivo_comprimido';
        } else {
            return 'otros';
        }
    }
}
