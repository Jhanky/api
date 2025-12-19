<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProjectState;
use Illuminate\Http\JsonResponse;

class ProjectStateController extends Controller
{
    /**
     * Listar todos los estados de proyecto
     */
    public function index(): JsonResponse
    {
        try {
            $states = ProjectState::where('is_active', true)
                ->orderBy('id')
                ->get()
                ->map(function ($state) {
                    // Mapear colores basados en el code/name del estado
                    $colorMap = [
                        'draft' => '#94a3b8',
                        'borrador' => '#94a3b8',
                        'planning' => '#3b82f6',
                        'planeacion' => '#3b82f6',
                        'in_progress' => '#f59e0b',
                        'ejecucion' => '#f59e0b',
                        'pending_legalization' => '#8b5cf6',
                        'legalizacion' => '#8b5cf6',
                        'completed' => '#16a34a',
                        'completado' => '#16a34a',
                        'cancelled' => '#dc2626',
                        'cancelado' => '#dc2626',
                    ];
                    
                    // Mapear fases basadas en el code/name
                    $phaseMap = [
                        'draft' => 'commercial',
                        'borrador' => 'commercial',
                        'planning' => 'technical',
                        'planeacion' => 'technical',
                        'in_progress' => 'technical',
                        'ejecucion' => 'technical',
                        'pending_legalization' => 'legal',
                        'legalizacion' => 'legal',
                        'completed' => 'completed',
                        'completado' => 'completed',
                        'cancelled' => 'cancelled',
                        'cancelado' => 'cancelled',
                    ];
                    
                    $codeKey = strtolower($state->code ?? $state->name ?? '');
                    
                    return [
                        'id' => $state->id,
                        'name' => $state->name,
                        'slug' => $state->code ?? strtolower(str_replace(' ', '_', $state->name)),
                        'description' => $state->description,
                        'color' => $colorMap[$codeKey] ?? '#6b7280',
                        'display_order' => $state->id,
                        'phase' => $phaseMap[$codeKey] ?? 'other',
                        'is_final' => in_array($codeKey, ['completed', 'completado', 'cancelled', 'cancelado']),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $states
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estados de proyecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un estado específico
     */
    public function show($id): JsonResponse
    {
        try {
            $state = ProjectState::findOrFail($id);
            
            $codeKey = strtolower($state->code ?? $state->name ?? '');
            
            $colorMap = [
                'draft' => '#94a3b8', 'borrador' => '#94a3b8',
                'planning' => '#3b82f6', 'planeacion' => '#3b82f6',
                'in_progress' => '#f59e0b', 'ejecucion' => '#f59e0b',
                'pending_legalization' => '#8b5cf6', 'legalizacion' => '#8b5cf6',
                'completed' => '#16a34a', 'completado' => '#16a34a',
                'cancelled' => '#dc2626', 'cancelado' => '#dc2626',
            ];
            
            $phaseMap = [
                'draft' => 'commercial', 'borrador' => 'commercial',
                'planning' => 'technical', 'planeacion' => 'technical',
                'in_progress' => 'technical', 'ejecucion' => 'technical',
                'pending_legalization' => 'legal', 'legalizacion' => 'legal',
                'completed' => 'completed', 'completado' => 'completed',
                'cancelled' => 'cancelled', 'cancelado' => 'cancelled',
            ];

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $state->id,
                    'name' => $state->name,
                    'slug' => $state->code ?? strtolower(str_replace(' ', '_', $state->name)),
                    'description' => $state->description,
                    'color' => $colorMap[$codeKey] ?? '#6b7280',
                    'display_order' => $state->id,
                    'phase' => $phaseMap[$codeKey] ?? 'other',
                    'is_final' => in_array($codeKey, ['completed', 'completado', 'cancelled', 'cancelado']),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Estado no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
