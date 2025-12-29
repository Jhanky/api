<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Quotation;
use App\Models\ProjectState;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Get paginated projects with filters
     */
    public function getProjects(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Project::with([
            'quotation.quotationProducts',
            'quotation.systemType',
            'quotation.gridType',
            'client',
            'currentState'
        ])
        ->whereHas('quotation') // Solo proyectos con cotización válida
        ->whereHas('client')    // Asegurar que el cliente existe
        ->whereHas('currentState'); // Asegurar que el estado existe

        // Apply filters
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('client', function ($clientQuery) use ($search) {
                      $clientQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if (!empty($filters['status'])) {
            $query->where('current_state_id', $filters['status']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get project by ID with relationships
     */
    public function getProjectById(int $id): ?Project
    {
        return Project::with([
            'quotation.quotationProducts',
            'quotation.systemType',
            'quotation.gridType',
            'client',
            'currentState'
        ])->find($id);
    }

    /**
     * Create new project
     */
    public function createProject(array $data): Project
    {
        DB::beginTransaction();
        try {
            // Validate quotation exists and is not already used
            $quotation = Quotation::findOrFail($data['quotation_id']);

            // Check if quotation is already assigned to another project
            $existingProject = Project::where('quotation_id', $data['quotation_id'])->first();
            if ($existingProject) {
                throw new \Exception('La cotización ya está asignada a otro proyecto');
            }

            $project = Project::create($data);

            // Create initial state history
            $project->projectStateHistory()->create([
                'to_state_id' => $project->current_state_id,
                'changed_by' => auth()->id() ?? 1,
                'changed_at' => now(),
                'started_at' => now(),
                'notes' => 'Creación inicial del proyecto'
            ]);

            DB::commit();
            return $project->load(['quotation', 'client', 'currentState']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update project
     */
    public function updateProject(int $id, array $data): Project
    {
        DB::beginTransaction();
        try {
            $project = Project::findOrFail($id);

            // If changing quotation, validate it's not used by another project
            if (isset($data['quotation_id']) && $data['quotation_id'] !== $project->quotation_id) {
                $existingProject = Project::where('quotation_id', $data['quotation_id'])
                    ->where('id', '!=', $id)
                    ->first();

                if ($existingProject) {
                    throw new \Exception('La cotización ya está asignada a otro proyecto');
                }
            }

            $project->update($data);

            DB::commit();
            return $project->fresh(['quotation', 'client', 'currentState']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete project
     */
    public function deleteProject(int $id): bool
    {
        DB::beginTransaction();
        try {
            $project = Project::findOrFail($id);

            // Eliminar registros relacionados
            $project->projectStateHistory()->delete();
            $project->notes()->delete();
            $project->technicalSpecs()->delete();
            $project->projectDocuments()->delete();
            $project->costCenter()->delete();
            $project->upmeDetail()->delete();
            $project->milestones()->delete();

            // Finalmente eliminar el proyecto
            $deleted = $project->delete();

            DB::commit();
            return $deleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update project status
     */
    public function updateProjectStatus(int $id, int $statusId, ?string $reason = null, ?string $notes = null): Project
    {
        $project = Project::findOrFail($id);
        $user = auth()->user();

        DB::beginTransaction();
        try {
            // Close current state history record
            $currentHistory = $project->projectStateHistory()->whereNull('ended_at')->latest()->first();
            if ($currentHistory) {
                $now = now();
                $duration = $currentHistory->started_at ? $currentHistory->started_at->diffInDays($now) : 0;
                $currentHistory->update([
                    'ended_at' => $now,
                    'duration_days' => max(1, $duration) // Al menos 1 día
                ]);
            }

            // Update project
            $oldStatusId = $project->current_state_id;
            $project->update(['current_state_id' => $statusId]);

            // Create new record
            $project->projectStateHistory()->create([
                'from_state_id' => $oldStatusId,
                'to_state_id' => $statusId,
                'changed_by' => $user ? $user->id : 1,
                'changed_at' => now(),
                'started_at' => now(),
                'reason' => $reason,
                'notes' => $notes,
            ]);

            DB::commit();
            return $project->fresh('currentState');
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get project statistics
     */
    public function getProjectStatistics(): array
    {
        $total = Project::count();

        // Contar por estado final vs en progreso
        $completed = Project::whereHas('currentState', function ($q) {
            $q->where('is_final', true);
        })->count();

        $inProgress = Project::whereHas('currentState', function ($q) {
            $q->where('is_final', false);
        })->count();

        // Calculate additional metrics
        $stats = Project::join('quotations', 'projects.quotation_id', '=', 'quotations.id')
            ->whereNull('projects.deleted_at')
            ->whereNull('quotations.deleted_at')
            ->select(
                DB::raw('SUM(quotations.total_value) as total_value'),
                DB::raw('SUM(quotations.power_kwp) as total_power_kwp')
            )
            ->first();

        // Calculate monthly trends for charts
        $monthlyStats = Project::join('quotations', 'projects.quotation_id', '=', 'quotations.id')
            ->leftJoin('project_states', 'projects.current_state_id', '=', 'project_states.id')
            ->whereNull('projects.deleted_at')
            ->whereNull('quotations.deleted_at')
            ->select(
                DB::raw("DATE_FORMAT(COALESCE(projects.estimated_end_date, projects.start_date), '%Y-%m') as month"),
                DB::raw('SUM(quotations.total_value) as projected_value'),
                DB::raw('SUM(CASE WHEN project_states.is_final = 1 THEN quotations.total_value ELSE 0 END) as realized_value'),
                DB::raw('COUNT(*) as project_count')
            )
            ->groupBy('month')
            ->orderBy('month')
            ->limit(12)
            ->get();

        return [
            'total' => $total,
            'in_progress' => $inProgress,
            'completed' => $completed,
            'total_value' => floatval($stats->total_value ?? 0),
            'total_power_kwp' => floatval($stats->total_power_kwp ?? 0),
            'monthly_trends' => $monthlyStats
        ];
    }

    /**
     * Format project for API response
     */
    public function formatProjectForApi(Project $project): array
    {
        try {
            $quotation = $project->quotation;
            $currentState = $project->currentState;
            $client = $project->client;

            // Basic project info
            $result = [
                'id' => $project->id,
                'name' => $project->name ?? 'Sin nombre',
                'code' => $project->code ?? ('PROY-' . str_pad($project->id, 6, '0', STR_PAD_LEFT)),
                'status' => $currentState ? [
                    'id' => $currentState->id,
                    'name' => $currentState->name ?? 'Sin estado',
                    'slug' => $currentState->slug ?? null,
                    'color' => $currentState->color ?? null,
                ] : null,
                'quotation_id' => $project->quotation_id,
                'client' => $client ? [
                    'id' => $client->id,
                    'name' => $client->name ?? 'Sin cliente',
                    'nic' => $client->nic ?? null,
                    'email' => $client->email ?? null,
                    'phone' => $client->phone ?? null,
                ] : null,
                'start_date' => $project->start_date?->format('Y-m-d'),
                'end_date' => $project->actual_end_date?->format('Y-m-d'),
                'notes' => $project->notes ?? null,
                'address' => $project->installation_address ?? null,
                'coordinates' => $project->coordinates ?? null,
            ];

            // Add quotation data if available
            if ($quotation) {
                $result = array_merge($result, [
                    'power_kwp' => floatval($quotation->power_kwp ?? 0),
                    'panel_count' => intval($quotation->panel_count ?? 0),
                    'total_value' => floatval($quotation->total_value ?? 0),
                    'system_type' => $quotation->systemType->name ?? null, // Map to root
                    'technical_info' => [
                        'system_type' => $quotation->systemType->name ?? null,
                        'grid_type' => $quotation->gridType->name ?? null,
                        'total_power' => floatval($quotation->power_kwp ?? 0),
                        'panel_count' => intval($quotation->panel_count ?? 0),
                        'budget' => floatval($quotation->total_value ?? 0),
                    ]
                ]);

                // Add products if available (simplified)
                $result['panels'] = [];
                $result['inverters'] = [];
                $result['batteries'] = [];

                if ($quotation->quotationProducts) {
                    foreach ($quotation->quotationProducts as $product) {
                        try {
                            // Safely get specs with error handling
                            $specs = $this->getProductSpecsSafely($product);

                            $productData = [
                                'brand' => $product->snapshot_brand ?? 'Sin marca',
                                'cantidad' => intval($product->quantity ?? 0),
                                'modelo' => $product->snapshot_model ?? 'Sin modelo',
                                'precio_unitario' => floatval($product->unit_price_cop ?? 0),
                                'valor_total' => floatval(($product->quantity ?? 0) * ($product->unit_price_cop ?? 0))
                            ];

                            switch ($product->product_type) {
                                case 'panel':
                                    $productData['potencia'] = $specs['power'] ?? null;
                                    $productData['tipo'] = $specs['type'] ?? null;
                                    $result['panels'][] = $productData;
                                    break;
                                case 'inverter':
                                    $productData['potencia'] = $specs['power'] ?? null;
                                    $result['inverters'][] = $productData;
                                    break;
                                case 'battery':
                                    $productData['capacidad'] = $specs['capacity'] ?? null;
                                    $productData['voltaje'] = $specs['voltage'] ?? null;
                                    $result['batteries'][] = $productData;
                                    break;
                            }
                        } catch (\Exception $e) {
                            // Log error but continue processing other products
                            \Log::warning('Error processing quotation product', [
                                'project_id' => $project->id,
                                'product_id' => $product->id,
                                'error' => $e->getMessage()
                            ]);
                            continue;
                        }
                    }
                }
            } else {
                // No quotation data
                $result = array_merge($result, [
                    'power_kwp' => null,
                    'panel_count' => null,
                    'total_value' => null,
                    'technical_info' => [
                        'system_type' => null,
                        'total_power' => null,
                        'panel_count' => null,
                        'budget' => null,
                    ],
                    'panels' => [],
                    'inverters' => [],
                    'batteries' => []
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error formatting project for API', [
                'project_id' => $project->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return minimal data if formatting fails
            return [
                'id' => $project->id,
                'name' => $project->name ?? 'Error al cargar',
                'code' => $project->code ?? 'ERROR',
                'status' => null,
                'quotation_id' => $project->quotation_id,
                'client' => null,
                'power_kwp' => null,
                'panel_count' => null,
                'total_value' => null,
                'start_date' => null,
                'end_date' => null,
                'notes' => 'Error al procesar datos del proyecto',
                'technical_info' => [],
                'panels' => [],
                'inverters' => [],
                'batteries' => []
            ];
        }
    }

    /**
     * Safely get product specs with comprehensive error handling
     */
    private function getProductSpecsSafely($product): array
    {
        try {
            // First try the accessor
            $specs = $product->specs;

            // Validate that specs is an array
            if (!is_array($specs)) {
                \Log::warning('Product specs is not an array', [
                    'product_id' => $product->id,
                    'specs_type' => gettype($specs),
                    'specs_value' => $specs
                ]);
                return [];
            }

            return $specs;
        } catch (\Exception $e) {
            \Log::error('Error getting product specs', [
                'product_id' => $product->id,
                'snapshot_specs' => $product->snapshot_specs,
                'error' => $e->getMessage()
            ]);

            // Try manual JSON decode as fallback
            try {
                if (is_string($product->snapshot_specs)) {
                    $decoded = json_decode($product->snapshot_specs, true);
                    if (is_array($decoded)) {
                        return $decoded;
                    }
                }
            } catch (\Exception $fallbackError) {
                \Log::error('Fallback JSON decode also failed', [
                    'product_id' => $product->id,
                    'error' => $fallbackError->getMessage()
                ]);
            }

            return [];
        }
    }
}
