<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectImageController extends Controller
{
    /**
     * Subir imagen de portada para un proyecto
     * 
     * @param Request $request
     * @param Project $project
     * @return JsonResponse
     */
    public function uploadCoverImage(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'cover_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:5120', // 5MB max
            'cover_image_alt' => 'nullable|string|max:255'
        ]);

        try {
            // Eliminar imagen anterior si existe
            if ($project->cover_image && Storage::disk('public')->exists($project->cover_image)) {
                Storage::disk('public')->delete($project->cover_image);
            }

            // Subir nueva imagen
            $image = $request->file('cover_image');
            $filename = 'projects/' . $project->project_id . '/' . Str::uuid() . '.' . $image->getClientOriginalExtension();
            
            $path = $image->storeAs('', $filename, 'public');

            // Actualizar proyecto
            $project->update([
                'cover_image' => $path,
                'cover_image_alt' => $request->cover_image_alt ?? 'Imagen de la planta solar ' . $project->project_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Imagen de portada subida exitosamente',
                'data' => [
                    'cover_image' => asset('storage/' . $path),
                    'cover_image_alt' => $project->cover_image_alt
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al subir la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar imagen de portada de un proyecto
     * 
     * @param Project $project
     * @return JsonResponse
     */
    public function deleteCoverImage(Project $project): JsonResponse
    {
        try {
            if ($project->cover_image && Storage::disk('public')->exists($project->cover_image)) {
                Storage::disk('public')->delete($project->cover_image);
            }

            $project->update([
                'cover_image' => null,
                'cover_image_alt' => null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Imagen de portada eliminada exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la imagen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener imagen de portada de un proyecto
     * 
     * @param Project $project
     * @return JsonResponse
     */
    public function getCoverImage(Project $project): JsonResponse
    {
        if (!$project->cover_image) {
            return response()->json([
                'success' => false,
                'message' => 'El proyecto no tiene imagen de portada'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cover_image' => asset('storage/' . $project->cover_image),
                'cover_image_alt' => $project->cover_image_alt
            ]
        ]);
    }
}