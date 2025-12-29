<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProjectService;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\UpdateProjectStatusRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    use ApiResponseTrait;

    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    /**
     * Get paginated projects with filters
     */
    public function index(): JsonResponse
    {
        try {
            $filters = request()->only(['search', 'status', 'client_id']);
            $perPage = request()->get('per_page', 15);

            $paginator = $this->projectService->getProjects($filters, $perPage);

            $formattedProjects = $paginator->getCollection()->map(function ($project) {
                return $this->projectService->formatProjectForApi($project);
            });

            // Crear nuevo paginator con los datos formateados
            $formattedPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
                $formattedProjects,
                $paginator->total(),
                $paginator->perPage(),
                $paginator->currentPage(),
                [
                    'path' => $paginator->path(),
                    'pageName' => $paginator->getPageName(),
                ]
            );

            return $this->paginationResponse(
                $formattedPaginator,
                'Proyectos obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener proyectos',
                [],
                500,
                'INTERNAL_ERROR'
            );
        }
    }

    /**
     * Create new project
     */
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $project = $this->projectService->createProject($request->validated());
            $formattedProject = $this->projectService->formatProjectForApi($project);

            return $this->successResponse(
                $formattedProject,
                'Proyecto creado exitosamente',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al crear proyecto: ' . $e->getMessage(),
                [],
                500,
                'INTERNAL_ERROR'
            );
        }
    }

    /**
     * Get project by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $project = $this->projectService->getProjectById($id);

            if (!$project) {
                return $this->notFoundResponse('Proyecto no encontrado');
            }

            $formattedProject = $this->projectService->formatProjectForApi($project);

            return $this->successResponse(
                $formattedProject,
                'Proyecto obtenido exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener proyecto',
                [],
                500,
                'INTERNAL_ERROR'
            );
        }
    }

    /**
     * Update project
     */
    public function update(UpdateProjectRequest $request, int $id): JsonResponse
    {
        try {
            $project = $this->projectService->updateProject($id, $request->validated());
            $formattedProject = $this->projectService->formatProjectForApi($project);

            return $this->successResponse(
                $formattedProject,
                'Proyecto actualizado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar proyecto: ' . $e->getMessage(),
                [],
                500,
                'INTERNAL_ERROR'
            );
        }
    }

    /**
     * Delete project
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $this->projectService->deleteProject($id);

            return $this->successResponse(
                null,
                'Proyecto eliminado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al eliminar proyecto: ' . $e->getMessage(),
                500,
                'INTERNAL_ERROR'
            );
        }
    }

    /**
     * Update project status
     */
    public function updateStatus(UpdateProjectStatusRequest $request, int $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $project = $this->projectService->updateProjectStatus(
                $id, 
                $validated['status_id'],
                $validated['reason'] ?? null,
                $validated['notes'] ?? null
            );
            $formattedProject = $this->projectService->formatProjectForApi($project);

            return $this->successResponse(
                $formattedProject,
                'Estado del proyecto actualizado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al actualizar estado del proyecto: ' . $e->getMessage(),
                [],
                500,
                'INTERNAL_ERROR'
            );
        }
    }

    /**
     * Get project statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->projectService->getProjectStatistics();

            return $this->successResponse(
                $stats,
                'Estadísticas obtenidas exitosamente'
            );
        } catch (\Exception $e) {
            return $this->errorResponse(
                'Error al obtener estadísticas',
                [],
                500,
                'INTERNAL_ERROR'
            );
        }
    }
}
