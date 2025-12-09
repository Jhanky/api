<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Listar proyectos para móvil (versión simplificada)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Project::with([
                'quotation:id,project_name,power_kwp',
                'client:id,name,client_type',
                'location:id,municipality,department',
                'status:id,name,color,is_active'
            ]);

            // Filtros básicos para móvil
            if ($request->has('status')) {
                $query->whereHas('status', function ($q) use ($request) {
                    $q->where('name', $request->status);
                });
            }

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->whereHas('quotation', function ($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%");
                });
            }

            // Paginación optimizada para móvil
            $perPage = min($request->get('per_page', 15), 50); // Máximo 50 por página
            $projects = $query->orderBy('updated_at', 'desc')->paginate($perPage);

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
                        'color' => $project->status->color ?? '#6B7280',
                        'is_active' => $project->status->is_active ?? false
                    ],
                    'client' => [
                        'name' => $project->client->name ?? 'Cliente no especificado',
                        'type' => $project->client->client_type ?? null
                    ],
                    'dates' => [
                        'start' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                        'estimated_end' => $project->estimated_end_date ? $project->estimated_end_date->format('Y-m-d') : null,
                        'actual_end' => $project->actual_end_date ? $project->actual_end_date->format('Y-m-d') : null
                    ],
                    'last_updated' => $project->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'projects' => $transformedProjects,
                    'pagination' => [
                        'current_page' => $projects->currentPage(),
                        'last_page' => $projects->lastPage(),
                        'per_page' => $projects->perPage(),
                        'total' => $projects->total(),
                        'has_more' => $projects->hasMorePages()
                    ]
                ],
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
     * Obtener proyecto específico para móvil
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $project = Project::with([
                'quotation:id,project_name,power_kwp,system_type',
                'client:id,name,client_type,nic,department,city',
                'location:id,municipality,department',
                'status:id,name,color,description,is_active',
                'projectManager:id,name,email'
            ])->find($id);

            if (!$project) {
                return response()->json([
                    'success' => false,
                    'message' => 'Proyecto no encontrado'
                ], 404);
            }

            $projectData = [
                'id' => $project->project_id,
                'name' => $project->quotation->project_name ?? 'Proyecto sin nombre',
                'location' => [
                    'municipality' => $project->location->municipality ?? null,
                    'department' => $project->location->department ?? null,
                    'full_address' => $project->location ? 
                        ($project->location->municipality . ', ' . $project->location->department) : 
                        'Ubicación no especificada'
                ],
                'coordinates' => [
                    'latitude' => $project->latitude,
                    'longitude' => $project->longitude
                ],
                'technical_specs' => [
                    'capacity' => round($project->quotation->power_kwp ?? 0, 1),
                    'system_type' => $project->quotation->system_type ?? null
                ],
                'status' => [
                    'id' => $project->status->status_id ?? null,
                    'name' => $project->status->name ?? 'Desconocido',
                    'color' => $project->status->color ?? '#6B7280',
                    'description' => $project->status->description ?? null,
                    'is_active' => $project->status->is_active ?? false
                ],
                'client' => [
                    'id' => $project->client->client_id ?? null,
                    'name' => $project->client->name ?? 'Cliente no especificado',
                    'type' => $project->client->client_type ?? null,
                    'nic' => $project->client->nic ?? null,
                    'location' => $project->client ? 
                        ($project->client->city . ', ' . $project->client->department) : 
                        'Ubicación no especificada'
                ],
                'project_manager' => [
                    'name' => $project->projectManager->name ?? 'No asignado',
                    'email' => $project->projectManager->email ?? null
                ],
                'dates' => [
                    'start' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                    'estimated_end' => $project->estimated_end_date ? $project->estimated_end_date->format('Y-m-d') : null,
                    'actual_end' => $project->actual_end_date ? $project->actual_end_date->format('Y-m-d') : null
                ],
                'budget' => $project->budget,
                'notes' => $project->notes,
                'cover_image' => $project->cover_image ? asset('storage/' . $project->cover_image) : null,
                'cover_image_alt' => $project->cover_image_alt,
                'created_at' => $project->created_at->toISOString(),
                'updated_at' => $project->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $projectData,
                'message' => 'Proyecto obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el proyecto: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proyectos por estado para móvil
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getByStatus(Request $request): JsonResponse
    {
        try {
            $status = $request->get('status');
            
            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado requerido'
                ], 400);
            }

            $projects = Project::with([
                'quotation:id,project_name,power_kwp',
                'client:id,name,client_type',
                'location:id,municipality,department',
                'status:id,name,color'
            ])->whereHas('status', function ($query) use ($status) {
                $query->where('name', $status);
            })->limit(20)->get();

            $transformedProjects = $projects->map(function ($project) {
                return [
                    'id' => $project->project_id,
                    'name' => $project->quotation->project_name ?? 'Proyecto sin nombre',
                    'location' => $project->location ? 
                        ($project->location->municipality . ', ' . $project->location->department) : 
                        'Ubicación no especificada',
                    'capacity' => round($project->quotation->power_kwp ?? 0, 1),
                    'client' => $project->client->name ?? 'Cliente no especificado',
                    'last_updated' => $project->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $status,
                    'count' => $projects->count(),
                    'projects' => $transformedProjects
                ],
                'message' => "Proyectos con estado '{$status}' obtenidos exitosamente"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los proyectos: ' . $e->getMessage()
            ], 500);
        }
    }
}
