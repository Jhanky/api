<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\UsedProduct;
use App\Models\ItemCotizacion;
use App\Models\Client;
use App\Models\User;
use App\Models\QuotationStatus;
use App\Models\Panel;
use App\Models\Inverter;
use App\Models\Battery;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class QuotationController extends Controller
{
    /**
     * 1. Listar Cotizaciones
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Quotation::with([
                'client:client_id,nic,client_type,name,department,city',
                'status:status_id,name,color'
            ]);

            // Filtros
            if ($request->has('status_id')) {
                $query->where('status_id', $request->status_id);
            }

            if ($request->has('system_type')) {
                $query->where('system_type', $request->system_type);
            }

            if ($request->has('client_id')) {
                $query->where('client_id', $request->client_id);
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('project_name', 'like', "%{$search}%")
                      ->orWhereHas('client', function($clientQuery) use ($search) {
                          $clientQuery->where('name', 'like', "%{$search}%")
                                    ->orWhere('nic', 'like', "%{$search}%");
                      });
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            $quotations = $query->paginate($request->get('per_page', 15));

            // Agregar número de cotización y datos adicionales
            $quotations->getCollection()->transform(function ($quotation) {
                $quotationArray = $quotation->toArray();
                $quotationArray['quotation_number'] = $quotation->quotation_number;
                return $quotationArray;
            });

            return response()->json([
                'success' => true,
                'data' => $quotations,
                'message' => 'Cotizaciones obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cotizaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. Obtener Más Información de Cotización
     */
    public function show($id): JsonResponse
    {
        try {
            $quotation = Quotation::with([
                'client',
                'status',
                'usedProducts',
                'items'
            ])->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            // Agregar información detallada de productos con marca y potencia
            $quotation->usedProducts->each(function ($usedProduct) {
                $product = $this->getProduct($usedProduct->product_type, $usedProduct->product_id);
                if ($product) {
                    $usedProduct->product = $product;
                    
                    // Agregar información específica de marca y potencia
                    $usedProduct->product_brand = $product->brand;
                    $usedProduct->product_model = $product->model;
                    
                    // Potencia según el tipo de producto
                    switch ($usedProduct->product_type) {
                        case 'panel':
                            $usedProduct->product_power = $product->power . ' W';
                            break;
                        case 'inverter':
                            $usedProduct->product_power = $product->power . ' W';
                            break;
                        case 'battery':
                            $usedProduct->product_power = $product->capacity . ' Ah / ' . $product->voltage . ' V';
                            break;
                        default:
                            $usedProduct->product_power = 'N/A';
                    }
                }
            });

            return response()->json([
                'success' => true,
                'data' => $quotation,
                'message' => 'Cotización obtenida exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. Crear Cotización
     * 
     * IMPORTANTE: El backend realiza TODOS los cálculos automáticamente
     * basándose en los productos, items y porcentajes enviados.
     * 
     * El frontend solo envía: productos con cantidades y precios, items con cantidades y precios,
     * y los porcentajes de ganancia. El backend calcula: subtotales, ganancias, IVA, 
     * gestión comercial, administración, contingencia, retenciones y total final.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:clients,client_id',
                'user_id' => 'required|exists:users,id',
                'project_name' => 'required|string|max:200',
                'system_type' => 'required|in:On-grid,Off-grid,Híbrido,Interconectado',
                'power_kwp' => 'required|numeric|min:0.1',
                'panel_count' => 'required|integer|min:1',
                'requires_financing' => 'sometimes|boolean',
                'profit_percentage' => 'required|numeric|min:0|max:1',
                'iva_profit_percentage' => 'required|numeric|min:0|max:1',
                'commercial_management_percentage' => 'required|numeric|min:0|max:1',
                'administration_percentage' => 'required|numeric|min:0|max:1',
                'contingency_percentage' => 'required|numeric|min:0|max:1',
                'withholding_percentage' => 'required|numeric|min:0|max:1',
                'status_id' => 'sometimes|exists:quotation_statuses,status_id',
                'products' => 'sometimes|array',
                'products.*.product_type' => 'required_with:products|in:panel,inverter,battery',
                'products.*.product_id' => 'required_with:products|integer',
                'products.*.quantity' => 'required_with:products|integer|min:1',
                'products.*.unit_price' => 'required_with:products|numeric|min:0',
                'products.*.profit_percentage' => 'required_with:products|numeric|min:0|max:1',
                'items' => 'sometimes|array',
                'items.*.description' => 'required_with:items|string|max:500',
                'items.*.item_type' => 'required_with:items|string|max:50',
                'items.*.quantity' => 'required_with:items|numeric|min:0.01',
                'items.*.unit' => 'required_with:items|string|max:20',
                'items.*.unit_price' => 'required_with:items|numeric|min:0',
                'items.*.profit_percentage' => 'required_with:items|numeric|min:0|max:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Crear la cotización con valores iniciales (se calcularán automáticamente)
            // Siempre asignar estado 1 (Borrador) por defecto
            $quotationData = $request->only([
                'client_id',
                'user_id',
                'project_name',
                'system_type',
                'power_kwp',
                'panel_count',
                'requires_financing',
                'profit_percentage',
                'iva_profit_percentage',
                'commercial_management_percentage',
                'administration_percentage',
                'contingency_percentage',
                'withholding_percentage',
            ]);
            
            // Asignar estado 1 (Borrador) por defecto
            $quotationData['status_id'] = 1;
            
            $quotation = Quotation::create($quotationData);

            // Crear productos utilizados si se enviaron
            if ($request->has('products')) {
                foreach ($request->products as $productData) {
                    // Calcular valores automáticamente
                    $partialValue = $productData['quantity'] * $productData['unit_price'];
                    $profit = $partialValue * $productData['profit_percentage'];
                    $totalValue = $partialValue + $profit;
                    
                    UsedProduct::create([
                        'quotation_id' => $quotation->quotation_id,
                        'product_type' => $productData['product_type'],
                        'product_id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                        'profit_percentage' => $productData['profit_percentage'],
                        'partial_value' => $partialValue,
                        'profit' => $profit,
                        'total_value' => $totalValue,
                    ]);
                }
            }

            // Crear items si se enviaron
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    // Calcular valores automáticamente
                    $partialValue = $itemData['quantity'] * $itemData['unit_price'];
                    $profit = $partialValue * $itemData['profit_percentage'];
                    $totalValue = $partialValue + $profit;
                    
                    ItemCotizacion::create([
                        'quotation_id' => $quotation->quotation_id,
                        'description' => $itemData['description'],
                        'item_type' => $itemData['item_type'],
                        'quantity' => $itemData['quantity'],
                        'unit' => $itemData['unit'],
                        'unit_price' => $itemData['unit_price'],
                        'profit_percentage' => $itemData['profit_percentage'],
                        'partial_value' => $partialValue,
                        'profit' => $profit,
                        'total_value' => $totalValue,
                    ]);
                }
            }

            // Calcular todos los totales de la cotización automáticamente
            $quotation->calculateTotals();
            
            // Cargar datos completos para la respuesta
            $quotation->load(['client', 'status', 'usedProducts', 'items']);
            
            return response()->json([
                'success' => true,
                'data' => $quotation,
                'message' => 'Cotización creada exitosamente con todos los cálculos realizados'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 4. Editar Cotización
     * 
     * IMPORTANTE: Cuando el frontend edita productos o items, debe enviar TODOS los valores recalculados
     * porque los cambios afectan subtotales, ganancias, IVA, gestión comercial, administración, 
     * contingencia, retenciones y total final.
     * 
     * El frontend debe recalcular y enviar: subtotal, profit, profit_iva, commercial_management,
     * administration, contingency, withholdings, total_value, subtotal2, subtotal3
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $quotation = Quotation::find($id);
            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'client_id' => 'sometimes|exists:clients,client_id',
                'user_id' => 'sometimes|exists:users,id',
                'project_name' => 'sometimes|string|max:200',
                'system_type' => 'sometimes|in:On-grid,Off-grid,Híbrido',
                'power_kwp' => 'sometimes|numeric|min:0.1',
                'panel_count' => 'sometimes|integer|min:1',
                'requires_financing' => 'sometimes|boolean',
                'profit_percentage' => 'sometimes|numeric|min:0|max:1',
                'iva_profit_percentage' => 'sometimes|numeric|min:0|max:1',
                'commercial_management_percentage' => 'sometimes|numeric|min:0|max:1',
                'administration_percentage' => 'sometimes|numeric|min:0|max:1',
                'contingency_percentage' => 'sometimes|numeric|min:0|max:1',
                'withholding_percentage' => 'sometimes|numeric|min:0|max:1',
                'status_id' => 'sometimes|exists:quotation_statuses,status_id',
                'subtotal' => 'sometimes|numeric|min:0',
                'profit' => 'sometimes|numeric|min:0',
                'profit_iva' => 'sometimes|numeric|min:0',
                'commercial_management' => 'sometimes|numeric|min:0',
                'administration' => 'sometimes|numeric|min:0',
                'contingency' => 'sometimes|numeric|min:0',
                'withholdings' => 'sometimes|numeric|min:0',
                'total_value' => 'sometimes|numeric|min:0',
                'subtotal2' => 'sometimes|numeric|min:0',
                'subtotal3' => 'sometimes|numeric|min:0',
                'used_products' => 'sometimes|array',
                'used_products.*.used_product_id' => 'sometimes|exists:used_products,used_product_id',
                'used_products.*.quantity' => 'sometimes|integer|min:1',
                'used_products.*.unit_price' => 'sometimes|numeric|min:0',
                'used_products.*.profit_percentage' => 'sometimes|numeric|min:0|max:1',
                'used_products.*.partial_value' => 'sometimes|numeric|min:0',
                'used_products.*.profit' => 'sometimes|numeric|min:0',
                'used_products.*.total_value' => 'sometimes|numeric|min:0',
                'items' => 'sometimes|array',
                'items.*.item_id' => 'sometimes|exists:quotation_items,item_id',
                'items.*.description' => 'sometimes|string|max:500',
                'items.*.item_type' => 'sometimes|string|max:50',
                'items.*.quantity' => 'sometimes|numeric|min:0.01',
                'items.*.unit' => 'sometimes|string|max:20',
                'items.*.unit_price' => 'sometimes|numeric|min:0',
                'items.*.profit_percentage' => 'sometimes|numeric|min:0|max:1',
                'items.*.partial_value' => 'sometimes|numeric|min:0',
                'items.*.profit' => 'sometimes|numeric|min:0',
                'items.*.total_value' => 'sometimes|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Actualizar solo los campos enviados
            $quotation->update($request->only([
                'client_id',
                'user_id',
                'project_name',
                'system_type',
                'power_kwp',
                'panel_count',
                'requires_financing',
                'profit_percentage',
                'iva_profit_percentage',
                'commercial_management_percentage',
                'administration_percentage',
                'contingency_percentage',
                'withholding_percentage',
                'subtotal',
                'profit',
                'profit_iva',
                'commercial_management',
                'administration',
                'contingency',
                'withholdings',
                'total_value',
                'subtotal2',
                'subtotal3',
                'status_id',
            ]));

            // Actualizar productos utilizados si se enviaron
            if ($request->has('used_products')) {
                foreach ($request->used_products as $productData) {
                    if (isset($productData['used_product_id'])) {
                        $usedProduct = UsedProduct::find($productData['used_product_id']);
                        if ($usedProduct && $usedProduct->quotation_id == $quotation->quotation_id) {
                            $usedProduct->update([
                                'quantity' => $productData['quantity'] ?? $usedProduct->quantity,
                                'unit_price' => $productData['unit_price'] ?? $usedProduct->unit_price,
                                'profit_percentage' => $productData['profit_percentage'] ?? $usedProduct->profit_percentage,
                                'partial_value' => $productData['partial_value'] ?? $usedProduct->partial_value,
                                'profit' => $productData['profit'] ?? $usedProduct->profit,
                                'total_value' => $productData['total_value'] ?? $usedProduct->total_value,
                            ]);
                        }
                    }
                }
            }

            // Actualizar items si se enviaron
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    if (isset($itemData['item_id'])) {
                        $item = ItemCotizacion::find($itemData['item_id']);
                        if ($item && $item->quotation_id == $quotation->quotation_id) {
                            $item->update([
                                'description' => $itemData['description'] ?? $item->description,
                                'item_type' => $itemData['item_type'] ?? $item->item_type,
                                'quantity' => $itemData['quantity'] ?? $item->quantity,
                                'unit' => $itemData['unit'] ?? $item->unit,
                                'unit_price' => $itemData['unit_price'] ?? $item->unit_price,
                                'profit_percentage' => $itemData['profit_percentage'] ?? $item->profit_percentage,
                                'partial_value' => $itemData['partial_value'] ?? $item->partial_value,
                                'profit' => $itemData['profit'] ?? $item->profit,
                                'total_value' => $itemData['total_value'] ?? $item->total_value,
                            ]);
                        }
                    }
                }
            }

            // Cargar datos actualizados para la respuesta
            $quotation->load(['usedProducts', 'items']);
            
            return response()->json([
                'success' => true,
                'data' => [
                    'quotation_id' => $quotation->quotation_id,
                    'client_id' => $quotation->client_id,
                    'user_id' => $quotation->user_id,
                    'project_name' => $quotation->project_name,
                    'system_type' => $quotation->system_type,
                    'power_kwp' => $quotation->power_kwp,
                    'panel_count' => $quotation->panel_count,
                    'requires_financing' => $quotation->requires_financing,
                    'profit_percentage' => $quotation->profit_percentage,
                    'iva_profit_percentage' => $quotation->iva_profit_percentage,
                    'commercial_management_percentage' => $quotation->commercial_management_percentage,
                    'administration_percentage' => $quotation->administration_percentage,
                    'contingency_percentage' => $quotation->contingency_percentage,
                    'withholding_percentage' => $quotation->withholding_percentage,
                    'status_id' => $quotation->status_id,
                    'subtotal' => $quotation->subtotal,
                    'profit' => $quotation->profit,
                    'profit_iva' => $quotation->profit_iva,
                    'commercial_management' => $quotation->commercial_management,
                    'administration' => $quotation->administration,
                    'contingency' => $quotation->contingency,
                    'withholdings' => $quotation->withholdings,
                    'total_value' => $quotation->total_value,
                    'subtotal2' => $quotation->subtotal2,
                    'subtotal3' => $quotation->subtotal3,
                    'updated_at' => $quotation->updated_at,
                    'used_products_count' => $quotation->usedProducts->count(),
                    'items_count' => $quotation->items->count()
                ],
                'message' => 'Cotización actualizada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 5. Eliminar Cotización
     */
    public function destroy($id): JsonResponse
    {
        try {
            $quotation = Quotation::find($id);
            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            // Las relaciones se eliminan automáticamente por cascade
            $quotation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cotización eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 6. Cambiar Estado de Cotización
     */
    public function updateStatus(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'status_id' => 'required|exists:quotation_statuses,status_id'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $quotation = Quotation::find($id);
            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            $status = QuotationStatus::find($request->status_id);
            if (!$status) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estado no válido'
                ], 422);
            }

            $oldStatusId = $quotation->status_id;
            $quotation->update(['status_id' => $request->status_id]);

            // Crear proyecto automáticamente si el estado cambió a "Contratada" (ID 5)
            $projectCreated = null;
            if ($oldStatusId != 5 && $request->status_id == 5) {
                $projectCreated = $this->createProjectFromQuotation($quotation);
            }

            $responseData = [
                'quotation_id' => $quotation->quotation_id,
                'status' => [
                    'status_id' => $status->status_id,
                    'name' => $status->name,
                    'description' => $status->description,
                    'color' => $status->color
                ],
                'updated_at' => $quotation->updated_at
            ];

            // Agregar información del proyecto si se creó
            if ($projectCreated) {
                $responseData['project_created'] = [
                    'project_id' => $projectCreated->project_id,
                    'project_name' => $projectCreated->project_name,
                    'status' => $projectCreated->status->name
                ];
            }

            $message = 'Estado de cotización actualizado exitosamente';
            if ($projectCreated) {
                $message .= ' y proyecto creado automáticamente';
            }

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado de cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Método auxiliar para obtener producto por tipo
     */
    private function getProduct($type, $id)
    {
        switch ($type) {
            case 'panel':
                return Panel::find($id);
            case 'inverter':
                return Inverter::find($id);
            case 'battery':
                return Battery::find($id);
            default:
                return null;
        }
    }

    /**
     * Crear proyecto automáticamente cuando la cotización se convierte en contratada
     */
    private function createProjectFromQuotation(Quotation $quotation)
    {
        try {
            // Verificar que no exista ya un proyecto para esta cotización
            if (Project::where('quotation_id', $quotation->quotation_id)->exists()) {
                \Log::warning('Ya existe un proyecto para la cotización #' . $quotation->quotation_id);
                return null;
            }

            // Obtener el estado "Activo" para el proyecto
            $initialStatus = ProjectStatus::where('name', 'Activo')->first();
            
            if (!$initialStatus) {
                // Si no existe el estado, usar el primero disponible
                $initialStatus = ProjectStatus::first();
            }

            // Obtener la ubicación del cliente
            $location = $quotation->client->location ?? Location::first();

            // Crear el proyecto
            $project = Project::create([
                'quotation_id' => $quotation->quotation_id,
                'client_id' => $quotation->client_id,
                'location_id' => $location ? $location->location_id : null,
                'status_id' => $initialStatus->status_id,
                'project_name' => $quotation->project_name,
                'start_date' => now(),
                'project_manager_id' => $quotation->user_id, // El usuario que creó la cotización será el gerente inicial
                'notes' => 'Proyecto creado automáticamente al contratar la cotización #' . $quotation->quotation_id . '. Pendiente visita técnica para geolocalización.',
                // Los campos de georreferenciación se actualizarán después mediante visita técnica
            ]);

            \Log::info('Proyecto creado exitosamente con ID: ' . $project->project_id . ' para cotización #' . $quotation->quotation_id);
            
            // Cargar las relaciones para la respuesta
            $project->load(['status']);
            
            return $project;
            
        } catch (\Exception $e) {
            \Log::error('Error al crear proyecto para cotización #' . $quotation->quotation_id . ': ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return null;
        }
    }
}