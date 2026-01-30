<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\SolarSimulationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    protected SolarSimulationService $solarService;

    public function __construct(SolarSimulationService $solarService)
    {
        $this->solarService = $solarService;
    }

    /**
     * Apply role-based filtering to the query
     */
    private function applyRoleFilter(Builder $query): Builder
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            return $query; // Admins see everything
        }

        return $query->where(function ($q) use ($user) {
            $q->where('project_manager_id', $user->id)
              ->orWhere('technical_leader_id', $user->id);
        });
    }

    /**
     * Obtener todos los proyectos para la página de inicio (con filtros de rol)
     */
    public function getProjects(): JsonResponse
    {
        try {
            $query = Project::with([
                'quotation:id,project_name,power_kwp', // Select only needed fields
                'client:id,name,city_id,department_id',
                'client.city',
                'client.department',
                'location:id,municipality,department,latitude,longitude',
                'status:id,name,is_active',
                'projectManager:id,name'
            ]);

            // Apply role-based filtering
            $query = $this->applyRoleFilter($query);

            $projects = $query->get();

            $transformedProjects = $projects->map(function ($project) {
                return $this->transformProjectForDashboard($project);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProjects,
                'message' => 'Proyectos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los proyectos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proyectos activos (con filtros de rol)
     */
    public function getActiveProjects(): JsonResponse
    {
        try {
            $query = Project::with([
                'quotation',
                'client',
                'location',
                'status',
                'projectManager'
            ])->whereHas('status', function ($q) {
                $q->where('is_active', true);
            });

            // Apply role-based filtering
            $query = $this->applyRoleFilter($query);

            $projects = $query->get();

            $transformedProjects = $projects->map(function ($project) {
                return $this->transformProjectForDashboard($project);
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
     * Obtener estadísticas generales del dashboard (Optimized SQL Aggregation)
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            // Base query with role filters
            $baseQuery = $this->applyRoleFilter(Project::query());
            
            // Total Projects
            $totalProjects = $baseQuery->count();

            // Active Projects Count
            $activeProjectsQuery = clone $baseQuery;
            $activeProjects = $activeProjectsQuery->whereHas('status', function ($q) {
                $q->where('is_active', true);
            })->count();
            
            // Completed Projects Count
            $completedProjectsQuery = clone $baseQuery;
            $completedProjects = $completedProjectsQuery->whereHas('status', function ($q) {
                $q->where('name', 'Completado');
            })->count();

            // Total Capacity using SQL Sum via JOIN with quotations
            $totalCapacityQuery = clone $baseQuery;
            $totalCapacity = $totalCapacityQuery->join('quotations', 'projects.quotation_id', '=', 'quotations.id')
                ->sum('quotations.power_kwp');

            // Active Capacity using SQL Sum
            $activeCapacityQuery = clone $baseQuery;
            $activeCapacity = $activeCapacityQuery->whereHas('status', function ($q) {
                    $q->where('is_active', true);
                })
                ->join('quotations', 'projects.quotation_id', '=', 'quotations.id')
                ->sum('quotations.power_kwp');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_projects' => $totalProjects,
                    'active_projects' => $activeProjects,
                    'completed_projects' => $completedProjects,
                    'total_capacity_kwp' => round($totalCapacity ?? 0, 2),
                    'active_capacity_kwp' => round($activeCapacity ?? 0, 2),
                    'efficiency_average' => $this->solarService->calculateAverageEfficiency(),
                    'last_updated' => Carbon::now()->toISOString()
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transformar un proyecto para el formato del dashboard
     */
    private function transformProjectForDashboard(Project $project): array
    {
        $location = $project->location;
        $ubicacion = $location ? 
            ($location->municipality . ', ' . $location->department) : 
            'Ubicación no especificada';

        $coordenadas = [];
        if ($project->latitude && $project->longitude) {
            $coordenadas = [(float)$project->latitude, (float)$project->longitude];
        }

        $capacidad = $project->quotation->power_kwp ?? 0;

        // Use service for calculations
        $potenciaActual = $this->solarService->simulateCurrentPower((float)$capacidad);
        $generacionHoy = $this->solarService->calculateTodayGeneration((float)$capacidad);
        $eficiencia = $this->solarService->calculateEfficiency();

        $estado = $this->mapProjectStatus($project->status->name ?? 'Desconocido');

        $cliente = $project->client;
        $clienteInfo = [
            'id' => $cliente->id ?? null,
            'nombre' => $cliente->name ?? 'Cliente no especificado',
            'ubicacion' => $cliente ? 
                (($cliente->city->name ?? '') . ', ' . ($cliente->department->name ?? '')) : 
                'Ubicación no especificada',
        ];

        return [
            'id' => $project->project_id, // Assuming project_id is the primary key or public ID
            'nombre' => $project->quotation->project_name ?? 'Proyecto sin nombre',
            'ubicacion' => $ubicacion,
            'coordenadas' => $coordenadas,
            'capacidad' => round($capacidad, 1),
            'potenciaActual' => round($potenciaActual, 1),
            'generacionHoy' => round($generacionHoy, 1),
            'estado' => $estado,
            'eficiencia' => $eficiencia,
            'ultimaActualizacion' => $project->updated_at->toISOString(),
            'fechaInicio' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
            'fechaFin' => $project->actual_end_date ? $project->actual_end_date->format('Y-m-d') : null,
            'imagenPortada' => $project->cover_image ? asset('storage/' . $project->cover_image) : null,
            'cliente' => $clienteInfo,
            'gerenteProyecto' => $project->projectManager->name ?? 'No asignado'
        ];
    }

    private function mapProjectStatus(string $projectStatus): string
    {
        $statusMap = [
            'En Progreso' => 'activa',
            'Activo' => 'activa',
            'Completado' => 'completada',
            'Pausado' => 'pausada',
            'Cancelado' => 'cancelada',
            'En Planificación' => 'planificacion'
        ];

        return $statusMap[$projectStatus] ?? 'desconocida';
    }
}
