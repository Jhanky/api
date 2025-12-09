<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener resumen del dashboard para móvil
     * 
     * @return JsonResponse
     */
    public function getMobileSummary(): JsonResponse
    {
        try {
            $totalProjects = Project::count();
            $activeProjects = Project::whereHas('status', function ($query) {
                $query->where('is_active', true);
            })->count();
            
            $completedProjects = Project::whereHas('status', function ($query) {
                $query->where('name', 'Completado');
            })->count();

            $totalCapacity = Project::whereHas('quotation')
                ->with('quotation')
                ->get()
                ->sum(function ($project) {
                    return $project->quotation->power_kwp ?? 0;
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'summary' => [
                        'total_projects' => $totalProjects,
                        'active_projects' => $activeProjects,
                        'completed_projects' => $completedProjects,
                        'total_capacity_kwp' => round($totalCapacity, 2)
                    ],
                    'last_updated' => Carbon::now()->toISOString()
                ],
                'message' => 'Resumen obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el resumen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proyectos activos para móvil (versión simplificada)
     * 
     * @return JsonResponse
     */
    public function getActiveProjects(): JsonResponse
    {
        try {
            $projects = Project::with([
                'quotation:id,project_name,power_kwp',
                'client:id,name,client_type',
                'location:id,municipality,department',
                'status:id,name,color'
            ])->whereHas('status', function ($query) {
                $query->where('is_active', true);
            })->limit(10)->get();

            $transformedProjects = $projects->map(function ($project) {
                return [
                    'id' => $project->project_id,
                    'name' => $project->quotation->project_name ?? 'Proyecto sin nombre',
                    'location' => $project->location ? 
                        ($project->location->municipality . ', ' . $project->location->department) : 
                        'Ubicación no especificada',
                    'capacity' => round($project->quotation->power_kwp ?? 0, 1),
                    'status' => [
                        'name' => $project->status->name ?? 'Desconocido',
                        'color' => $project->status->color ?? '#6B7280'
                    ],
                    'client' => [
                        'name' => $project->client->name ?? 'Cliente no especificado',
                        'type' => $project->client->client_type ?? null
                    ],
                    'last_updated' => $project->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProjects,
                'message' => 'Proyectos activos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los proyectos activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos de monitoreo en tiempo real para móvil
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getRealTimeData(Request $request): JsonResponse
    {
        try {
            $projectId = $request->get('project_id');
            
            if (!$projectId) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de proyecto requerido'
                ], 400);
            }

            $project = Project::with(['quotation:id,power_kwp'])
                ->find($projectId);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyecto no encontrado'
                ], 404);
            }

            $capacidad = $project->quotation->power_kwp ?? 0;
            
            // Simulación de datos en tiempo real (simplificada para móvil)
            $currentPower = $this->simulateCurrentPower($capacidad);
            $todayGeneration = $this->calculateTodayGeneration($capacidad);
            $efficiency = $this->calculateEfficiency();

            return response()->json([
                'success' => true,
                'data' => [
                    'project_id' => $project->project_id,
                    'current_power' => round($currentPower, 1),
                    'today_generation' => round($todayGeneration, 1),
                    'efficiency' => $efficiency,
                    'timestamp' => Carbon::now()->toISOString()
                ],
                'message' => 'Datos en tiempo real obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos en tiempo real: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Simular potencia actual (versión simplificada para móvil)
     * 
     * @param float $capacidad
     * @return float
     */
    private function simulateCurrentPower(float $capacidad): float
    {
        $horaActual = Carbon::now()->hour + (Carbon::now()->minute / 60.0);
        
        if ($horaActual < 6 || $horaActual > 18) {
            return 0.0;
        }
        
        $ppico = $capacidad * 0.85;
        $factorSinusoidal = sin(M_PI * ($horaActual - 6) / 12);
        
        return max(0.0, $ppico * $factorSinusoidal);
    }

    /**
     * Calcular generación diaria (versión simplificada para móvil)
     * 
     * @param float $capacidad
     * @return float
     */
    private function calculateTodayGeneration(float $capacidad): float
    {
        return $capacidad * 4.5 * 0.85;
    }

    /**
     * Calcular eficiencia (versión simplificada para móvil)
     * 
     * @return int
     */
    private function calculateEfficiency(): int
    {
        return rand(80, 90);
    }
}
