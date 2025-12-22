<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationProduct;
use App\Models\Client;
use App\Models\User;
use App\Models\QuotationStatus;
use App\Models\Panel;
use App\Models\Inverter;
use App\Models\Battery;
use App\Models\Project;
use App\Models\ProjectState;
use App\Models\CostCenter;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class QuotationService
{
    /**
     * Get paginated quotations with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getQuotations(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        try {
            // Iniciar query con relaciones
            $query = Quotation::with([
                'client.clientType',
                'status',
                'user',
                'project',
                'systemType',     // Relación útil para mostrar tipo de sistema
                'gridType'        // Relación útil para mostrar tipo de red
            ]);

            // Aplicar filtros
            $this->applyFilters($query, $filters);

            // Ordenamiento por defecto
            $query->orderBy('created_at', 'desc');

            // Paginar resultados
            $perPageActual = $filters['per_page'] ?? $perPage;
            $paginated = $query->paginate($perPageActual);

            Log::info('✅ [QuotationService] getQuotations success', [
                'total_results' => $paginated->total(),
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'has_items' => $paginated->count() > 0,
                'filters_applied' => $filters
            ]);

            return $paginated;

        } catch (\Exception $e) {
            Log::error('❌ [QuotationService] getQuotations error', [
                'filters' => $filters,
                'perPage' => $perPage,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Get quotation by ID with full relationships
     *
     * @param int $id
     * @return Quotation
     */
    public function getQuotationById(int $id): Quotation
    {
        return Quotation::with([
            'client' => function($q) {
                $q->with('clientType');
            },
            'status',
            'systemType',
            'gridType',
            'quotationProducts',
            'quotationItems',
            'user'
        ])->findOrFail($id);
    }

    /**
     * Create a new quotation
     *
     * @param array $data
     * @return Quotation
     */
    public function createQuotation(array $data): Quotation
    {
        DB::beginTransaction();
        try {
            // Generate unique code
            $data['code'] = $this->generateQuotationCode();
            $data['status_id'] = 1; // Borrador
            $data['issue_date'] = now();
            $data['expiration_date'] = now()->addDays(15);

            $quotation = Quotation::create($data);

            // Create products if provided
            if (isset($data['products'])) {
                $this->createQuotationProducts($quotation, $data['products']);
            }

            // Create items if provided
            if (isset($data['items'])) {
                $this->createQuotationItems($quotation, $data['items']);
            }

            // Calculate totals and update quotation
            $this->calculateTotals($quotation);

            DB::commit();

            // Invalidate cache
            Cache::forget('quotation_statistics');

            return $quotation->load(['client', 'status', 'quotationProducts', 'quotationItems']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating quotation', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing quotation
     *
     * @param Quotation $quotation
     * @param array $data
     * @return Quotation
     */
    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        DB::beginTransaction();
        try {
            $quotation->update($data);

            // Update products if provided
            if (isset($data['products'])) {
                $this->updateQuotationProducts($quotation, $data['products']);
            }

            // Update items if provided
            if (isset($data['items'])) {
                $this->updateQuotationItems($quotation, $data['items']);
            }

            // Calculate totals and update quotation
            $this->calculateTotals($quotation);

            DB::commit();

            // Invalidate cache
            Cache::forget('quotation_statistics');

            return $quotation->fresh(['client', 'status', 'quotationProducts', 'quotationItems']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating quotation', [
                'quotation_id' => $quotation->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Calculate and save quotation totals
     *
     * @param Quotation $quotation
     * @return void
     */
    private function calculateTotals(Quotation $quotation): void
    {
        // Reload relationships to ensure we have the latest items and products
        $quotation->load(['quotationProducts', 'quotationItems']);

        $subtotal = 0;

        // Calculate subtotal from products (Cost + Profit per item)
        foreach ($quotation->quotationProducts as $product) {
            $cost = $product->quantity * $product->unit_price_cop;
            $profit = $cost * $product->profit_percentage;
            $subtotal += ($cost + $profit);
        }

        // Calculate subtotal from items (Cost + Profit per item)
        foreach ($quotation->quotationItems as $item) {
            $cost = $item->quantity * $item->unit_price_cop;
            $profit = $cost * $item->profit_percentage;
            $subtotal += ($cost + $profit);
        }

        // Round subtotal
        $subtotal = round($subtotal, 2);

        // Calculate percentages values
        $commercialManagement = round($subtotal * $quotation->commercial_management_percentage, 2);
        
        $subtotal2 = round($subtotal + $commercialManagement, 2);
        
        $administration = round($subtotal2 * $quotation->administration_percentage, 2);
        $contingency = round($subtotal2 * $quotation->contingency_percentage, 2);
        
        $profit = round($subtotal2 * $quotation->profit_percentage, 2);
        $profitIva = round($profit * $quotation->iva_profit_percentage, 2);
        
        $subtotal3 = round($subtotal2 + $administration + $contingency + $profit + $profitIva, 2);
        
        $withholdings = round($subtotal3 * $quotation->withholding_percentage, 2);
        
        $totalValue = round($subtotal3 + $withholdings, 2);

        // Update quotation with calculated values
        $quotation->update([
            'subtotal' => $subtotal,
            'commercial_management' => $commercialManagement,
            'subtotal2' => $subtotal2,
            'administration' => $administration,
            'contingency' => $contingency,
            'profit' => $profit,
            'profit_iva' => $profitIva,
            'subtotal3' => $subtotal3,
            'withholdings' => $withholdings,
            'total_value' => $totalValue
        ]);
    }

    /**
     * Delete a quotation
     *
     * @param Quotation $quotation
     * @return bool
     */
    public function deleteQuotation(Quotation $quotation): bool
    {
        DB::beginTransaction();
        try {
            // Delete related records
            $quotation->quotationProducts()->delete();
            $quotation->quotationItems()->delete();

            $quotation->delete();

            DB::commit();

            // Invalidate cache
            Cache::forget('quotation_statistics');

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting quotation', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update quotation status
     *
     * @param Quotation $quotation
     * @param int $statusId
     * @return array
     */
    public function updateQuotationStatus(Quotation $quotation, int $statusId): array
    {
        $oldStatusId = $quotation->status_id;
        $quotation->update(['status_id' => $statusId]);

        $response = [
            'quotation_id' => $quotation->id,
            'status' => QuotationStatus::find($statusId),
            'updated_at' => $quotation->updated_at
        ];

        // Create project automatically if status changed to "Aprobada" (ID 3)
        if ($oldStatusId != 3 && $statusId == 3) {
            $project = $this->createProjectFromQuotation($quotation);
            if ($project) {
                $response['project_created'] = [
                    'project_id' => $project->id,
                    'project_name' => $project->name,
                    'status' => $project->currentState->name ?? 'Sin estado'
                ];
                if (isset($project->cost_center)) {
                    $response['cost_center_created'] = [
                        'cost_center_id' => $project->cost_center->cost_center_id,
                        'code' => $project->cost_center->code,
                        'name' => $project->cost_center->name
                    ];
                }
            }
        }

        // Invalidate cache
        Cache::forget('quotation_statistics');

        return $response;
    }

    /**
     * Get quotation statistics with caching
     *
     * @return array
     */
    public function getQuotationStatistics(): array
    {
        return Cache::remember('quotation_statistics', 300, function () {
            return [
                'total' => Quotation::count(),
                'active_quotations' => Quotation::active()->count(),
                'by_status' => Quotation::selectRaw('status_id, COUNT(*) as count')
                                                ->with('status:id,name,color')
                                                ->groupBy('status_id')
                                                ->get(),
                'sum_total_value' => Quotation::sum('total_value'),
                'average_value' => Quotation::avg('total_value'),
                'sum_power_kwp' => Quotation::sum('power_kwp')
            ];
        });
    }

    /**
     * Get quotation data for PDF generation
     *
     * @param int $id
     * @return array
     */
    public function getQuotationPdfData(int $id): array
    {
        $quotation = Quotation::with([
            'client' => function($q) {
                $q->with(['clientType', 'city.department']);
            },
            'status',
            'systemType',
            'gridType',
            'quotationProducts',
            'quotationItems',
            'user'
        ])->findOrFail($id);

        // Transform products
        $supplies = $quotation->quotationProducts->map(function($product) {
            $productInfo = $this->getProduct($product->product_type, $product->product_id);
            $typeDisplay = ucfirst($product->product_type);

            switch ($product->product_type) {
                case 'panel': $typeDisplay = 'Panel Solar'; break;
                case 'inverter': $typeDisplay = 'Inversor'; break;
                case 'battery': $typeDisplay = 'Batería'; break;
            }

            return [
                'tipo' => $typeDisplay,
                'descripcion' => ($product->snapshot_brand ?? '') . ' ' . ($product->snapshot_model ?? ''),
                'cantidad' => (float) $product->quantity,
                'precio_unitario' => (float) $product->unit_price_cop,
                'porcentaje_utilidad' => (float) ($product->profit_percentage * 100),
                'valor_parcial' => (float) $product->quantity * $product->unit_price_cop,
                'utilidad' => (float) ($product->quantity * $product->unit_price_cop * $product->profit_percentage),
                'total' => (float) ($product->quantity * $product->unit_price_cop * (1 + $product->profit_percentage))
            ];
        })->toArray();

        // Transform items
        $complementaryItems = $quotation->quotationItems->map(function($item) {
            return [
                'descripcion' => $item->description,
                'cantidad' => (float) $item->quantity,
                'unidad' => $item->unit_measure ?? 'und',
                'precio_unitario' => (float) $item->unit_price_cop,
                'porcentaje_utilidad' => (float) ($item->profit_percentage * 100),
                'valor_parcial' => (float) $item->quantity * $item->unit_price_cop,
                'utilidad' => (float) ($item->quantity * $item->unit_price_cop * $item->profit_percentage),
                'total' => (float) ($item->quantity * $item->unit_price_cop * (1 + $item->profit_percentage))
            ];
        })->toArray();

        return [
            'id' => $quotation->id,
            'numero' => $quotation->code,
            'proyecto' => $quotation->project_name,
            'tipo_sistema' => $quotation->systemType?->name ?? 'On-grid',
            'tipo_red' => $quotation->gridType?->name ?? 'Monofásico',
            'potencia_total' => (float) $quotation->power_kwp,
            'requiere_financing' => (bool) $quotation->requires_financing,
            'cliente' => $quotation->client ? [
                'id' => $quotation->client->id,
                'name' => $quotation->client->name,
                'type' => $quotation->client->clientType?->slug ?? 'residencial',
                'email' => $quotation->client->email,
                'phone' => $quotation->client->cellphone ?? $quotation->client->phone,
                'full_address' => $quotation->client->address,
                'city' => ['name' => $quotation->client->city?->name ?? 'No disponible'],
                'department' => ['name' => $quotation->client->city?->department?->name ?? 'No disponible'],
                'monthly_consumption' => $quotation->client->monthly_consumption ?? 0
            ] : null,
            'vendedor' => $quotation->user?->name ?? 'No asignado',
            'estado' => $quotation->status?->name ?? 'Borrador',
            'fecha_creacion' => $quotation->created_at?->toIso8601String(),
            'fecha_vencimiento' => $quotation->expiration_date?->toIso8601String(),
            'suministros' => $supplies,
            'items_complementarios' => $complementaryItems,
            'subtotal' => (float) $quotation->subtotal,
            'profit' => (float) $quotation->profit,
            'profit_iva' => (float) $quotation->profit_iva,
            'commercial_management' => (float) $quotation->commercial_management,
            'administration' => (float) $quotation->administration,
            'contingency' => (float) $quotation->contingency,
            'withholdings' => (float) $quotation->withholdings,
            'subtotal2' => (float) $quotation->subtotal2,
            'subtotal3' => (float) $quotation->subtotal3,
            'valor_total' => (float) $quotation->total_value,
            'porcentaje_utilidad' => (float) ($quotation->profit_percentage * 100),
            'porcentaje_gestion_comercial' => (float) ($quotation->commercial_management_percentage * 100),
            'porcentaje_administracion' => (float) ($quotation->administration_percentage * 100),
            'porcentaje_imprevistos' => (float) ($quotation->contingency_percentage * 100),
            'porcentaje_retencion' => (float) ($quotation->withholding_percentage * 100),
        ];
    }

    /**
     * Apply filters to quotation query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        if (!empty($filters['status'])) {
            $query->whereHas('status', fn($sq) => $sq->where('name', $filters['status']));
        }

        if (!empty($filters['system_type']) && is_numeric($filters['system_type'])) {
            $query->where('system_type_id', $filters['system_type']);
        }

        if (!empty($filters['client_id'])) {
            $query->where('client_id', $filters['client_id']);
        }

        if (!empty($filters['client_type_id'])) {
            $query->whereHas('client', function($sq) use ($filters) {
                $sq->where('client_type_id', $filters['client_type_id']);
            });
        }

        if (!empty($filters['seller'])) {
            $query->whereHas('user', fn($sq) => $sq->where('name', 'like', "%{$filters['seller']}%"));
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($subq) use ($search) {
                $subq->where('project_name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($clientQ) use ($search) {
                        $clientQ->where('name', 'like', "%{$search}%")
                               ->orWhere('nic', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Generate unique quotation code
     *
     * @return string
     */
    private function generateQuotationCode(): string
    {
        $year = date('Y');
        // IMPORTANTE: Incluir registros eliminados (withTrashed) porque la restricción unique
        // de la base de datos aplica a TODOS los registros, incluso los soft-deleted
        $lastQuotation = Quotation::withTrashed()
            ->whereYear('created_at', $year)
            ->where('code', 'like', "COT-$year-%")
            ->orderBy('code', 'desc')
            ->first();

        $sequence = 1;
        if ($lastQuotation && preg_match("/COT-$year-(\\d+)/", $lastQuotation->code, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return "COT-$year-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Create quotation products
     *
     * @param Quotation $quotation
     * @param array $products
     * @return void
     */
    private function createQuotationProducts(Quotation $quotation, array $products): void
    {
        foreach ($products as $productData) {
            $product = $this->getProduct($productData['product_type'], $productData['product_id']);

            QuotationProduct::create([
                'quotation_id' => $quotation->id,
                'product_type' => $productData['product_type'],
                'product_id' => $productData['product_id'],
                'snapshot_brand' => $product ? $product->brand : null,
                'snapshot_model' => $product ? $product->model : null,
                'snapshot_specs' => $product ? $this->getProductSpecs($product, $productData['product_type']) : null,
                'quantity' => $productData['quantity'],
                'unit_price_cop' => $productData['unit_price_cop'] ?? $productData['unit_price'] ?? 0,
                'profit_percentage' => $productData['profit_percentage'],
            ]);
        }
    }

    /**
     * Update quotation products
     *
     * @param Quotation $quotation
     * @param array $products
     * @return void
     */
    private function updateQuotationProducts(Quotation $quotation, array $products): void
    {
        // Delete existing products
        $quotation->quotationProducts()->delete();

        // Create new products
        $this->createQuotationProducts($quotation, $products);
    }

    /**
     * Create quotation items
     *
     * @param Quotation $quotation
     * @param array $items
     * @return void
     */
    private function createQuotationItems(Quotation $quotation, array $items): void
    {
        foreach ($items as $itemData) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'description' => $itemData['description'],
                'category' => $itemData['category'] ?? $itemData['item_type'] ?? 'material',
                'quantity' => $itemData['quantity'],
                'unit_measure' => $itemData['unit_measure'] ?? $itemData['unit'] ?? 'unidad',
                'unit_price_cop' => $itemData['unit_price_cop'] ?? $itemData['unit_price'] ?? 0,
                'profit_percentage' => $itemData['profit_percentage'],
            ]);
        }
    }

    /**
     * Update quotation items
     *
     * @param Quotation $quotation
     * @param array $items
     * @return void
     */
    private function updateQuotationItems(Quotation $quotation, array $items): void
    {
        // Delete existing items
        $quotation->quotationItems()->delete();

        // Create new items
        $this->createQuotationItems($quotation, $items);
    }

    /**
     * Get product by type and ID
     *
     * @param string $type
     * @param int $id
     * @return mixed
     */
    private function getProduct(string $type, int $id)
    {
        switch ($type) {
            case 'panel': return Panel::find($id);
            case 'inverter': return Inverter::find($id);
            case 'battery': return Battery::find($id);
            default: return null;
        }
    }

    /**
     * Get product specifications for snapshot
     *
     * @param mixed $product
     * @param string $type
     * @return array|null
     */
    private function getProductSpecs($product, string $type): ?array
    {
        switch ($type) {
            case 'panel':
                return [
                    'power' => $product->power,
                    'type' => $product->type ?? null,
                ];
            case 'inverter':
                return [
                    'power' => $product->power,
                    'system_type' => $product->system_type ?? null,
                    'grid_type' => $product->grid_type ?? null,
                ];
            case 'battery':
                return [
                    'capacity' => $product->capacity,
                    'voltage' => $product->voltage,
                    'type' => $product->type ?? null,
                ];
            default:
                return null;
        }
    }

    /**
     * Create project automatically from approved quotation
     *
     * @param Quotation $quotation
     * @return Project|null
     */
    private function createProjectFromQuotation(Quotation $quotation): ?Project
    {
        try {
            // Check if project already exists
            $existingProject = Project::where('quotation_id', $quotation->id)->first();
            if ($existingProject) {
                return null;
            }

            // Validate client
            if (!$quotation->client) {
                return null;
            }

            // Get initial state
            $initialState = ProjectState::find(1);
            if (!$initialState) {
                return null;
            }

            // Generate project code
            $year = date('Y');
            $lastProject = Project::withTrashed()
                ->whereYear('created_at', $year)
                ->where('code', 'like', "PRO-$year-%")
                ->orderBy('id', 'desc')
                ->first();

            $sequence = 1;
            if ($lastProject && preg_match("/PRO-$year-(\d+)/", $lastProject->code, $matches)) {
                $sequence = intval($matches[1]) + 1;
            }

            $projectCode = "PRO-$year-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Determine project type based on client type
            $clientType = $quotation->client->clientType;
            $projectTypeId = 1; // Default: Residencial
            if ($clientType) {
                $clientTypeSlug = strtolower($clientType->slug ?? '');
                if (str_contains($clientTypeSlug, 'comercial') || str_contains($clientTypeSlug, 'empresa')) {
                    $projectTypeId = 2; // Comercial
                } elseif (str_contains($clientTypeSlug, 'industrial')) {
                    $projectTypeId = 3; // Industrial
                } elseif (str_contains($clientTypeSlug, 'institucional')) {
                    $projectTypeId = 4; // Institucional
                }
            }

            // Create project
            $project = Project::create([
                'code' => $projectCode,
                'quotation_id' => $quotation->id,
                'client_id' => $quotation->client_id,
                'project_type_id' => $projectTypeId,
                'department_id' => $quotation->client->department_id,
                'city_id' => $quotation->client->city_id,
                'current_state_id' => $initialState->id,
                'name' => $quotation->project_name,
                'installation_address' => $quotation->client->address ?? 'Pendiente de definir',
                'contracted_value_cop' => $quotation->total_value,
                'start_date' => now(),
                'project_manager_id' => $quotation->user_id,
                'notes' => 'Proyecto creado automáticamente al aprobar la cotización #' . $quotation->code . '. Pendiente visita técnica para geolocalización.',
                'is_active' => true,
            ]);

            // Create cost center
            $costCenterCode = 'CC-' . $projectCode;
            $costCenter = CostCenter::create([
                'code' => $costCenterCode,
                'name' => $quotation->project_name,
                'description' => 'Centro de costo para proyecto ' . $projectCode,
                'department_id' => $quotation->client->department_id,
                'project_id' => $project->id,
                'is_active' => true,
            ]);

            $project->load(['currentState']);
            $project->cost_center = $costCenter;

            return $project;

        } catch (\Exception $e) {
            Log::error('Error creating project from quotation', [
                'quotation_id' => $quotation->id,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
