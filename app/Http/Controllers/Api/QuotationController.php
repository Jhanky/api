<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationItem;
use App\Models\Client;
use App\Models\User;
use App\Models\QuotationStatus;
use App\Models\Panel;
use App\Models\Inverter;
use App\Models\Battery;
use App\Models\Project;
use App\Models\ProjectState;
use App\Models\CostCenter;
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
            // Inicializar consulta optimizada
            $query = Quotation::query()
                ->select([
                    'quotations.id',
                    'quotations.code',
                    'quotations.client_id',
                    'quotations.user_id',
                    'quotations.status_id',
                    'quotations.project_name',
                    'quotations.system_type_id', // Agregar para referencia
                    'quotations.grid_type_id',   // Agregar para referencia
                    'quotations.power_kwp',
                    'quotations.total_value', // Agregar valor total guardado
                    'quotations.created_at',
                    'quotations.updated_at'
                ])
                ->with([
                    'client' => function($q) {
                        $q->select('id', 'name', 'email', 'client_type_id', 'nic') // Seleccionar client_type_id
                          ->with('clientType:id,name,slug'); // Cargar relación nested para obtener el nombre/slug
                    },
                    'status:id,name,color', // Corregido: La tabla usa id, no status_id
                    'user:id,name',
                    'systemType:id,name', // Cargar nombres de tipos si se necesitan
                    'gridType:id,name'
                ])
                // Cargar sumas para calcular total_value sin traer todos los items
                ->withSum('quotationItems as items_total', DB::raw('quantity * unit_price_cop'))
                ->withSum('quotationProducts as products_total', DB::raw('quantity * unit_price_cop'));

            // --- Filtros ---

            // Estado (ID o Nombre)
            $query->when($request->filled('status_id'), function ($q) use ($request) {
                $q->where('status_id', $request->status_id);
            });
            $query->when($request->filled('status'), function ($q) use ($request) {
                $q->whereHas('status', fn($sq) => $sq->where('name', $request->status));
            });

            // Tipo de Sistema
            $query->when($request->filled('system_type') && is_numeric($request->system_type), function ($q) use ($request) {
                $q->where('system_type_id', $request->system_type);
            });

            // Cliente (ID o Tipo)
            $query->when($request->filled('client_id'), function ($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
            $query->when($request->filled('client_type'), function ($q) use ($request) {
                $clientType = $request->client_type;
                // Buscar por el slug o nombre del tipo de cliente a través de la relación client -> clientType
                $q->whereHas('client.clientType', function($sq) use ($clientType) {
                    $sq->where('slug', $clientType)
                       ->orWhere('name', $clientType);
                });
            });

            // Vendedor
            $query->when($request->filled('seller'), function ($q) use ($request) {
                $q->whereHas('user', fn($sq) => $sq->where('name', 'like', "%{$request->seller}%"));
            });

            // Búsqueda General
            $query->when($request->filled('search'), function ($q) use ($request) {
                $search = $request->search;
                $q->where(function ($subq) use ($search) {
                    $subq->where('project_name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('client', function ($clientQ) use ($search) {
                            $clientQ->where('name', 'like', "%{$search}%")
                                   ->orWhere('nic', 'like', "%{$search}%");
                        });
                });
            });

            // --- Ordenamiento ---
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $allowedSorts = ['created_at', 'updated_at', 'project_name', 'power_kwp', 'id', 'code'];
            
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // --- Paginación y Logs ---
            \DB::enableQueryLog();
            $perPage = $request->get('per_page', 15);
            $quotations = $query->paginate($perPage);
            
            \Log::info('🔍 SQL Index:', \DB::getQueryLog());
            \Log::info('📊 Resultados:', ['count' => $quotations->count(), 'total' => $quotations->total()]);

            // --- Transformación de Datos ---
            $quotations->getCollection()->transform(function ($quotation) {
                // Usar valor guardado si existe, sino calcular (fallback para cotizaciones antiguas)
                $rawValue = $quotation->total_value > 0 
                ? $quotation->total_value 
                : ($quotation->items_total ?? 0) + ($quotation->products_total ?? 0);
                
                // Procesar cliente para aplanar el tipo (para frontend)
                $clientData = $quotation->client ? $quotation->client->toArray() : null;
                if ($clientData && $quotation->client->clientType) {
                    $clientData['type'] = $quotation->client->clientType->slug; // O name, según lo que espere el front ('empresa', etc)
                    $clientData['type_name'] = $quotation->client->clientType->name;
                }
                
                return [
                    'id' => $quotation->id,
                    'quotation_number' => $quotation->code,
                    'project_name' => $quotation->project_name,
                    'total_power_kw' => $quotation->power_kwp,
                    'total_value' => $rawValue, // Valor real de la BD
                    'created_at' => $quotation->created_at,
                    'client' => $clientData, // Cliente con type aplanado
                    'status' => $quotation->status,
                    'user' => $quotation->user,
                    'system_type' => $quotation->systemType, // Extra info
                    'grid_type' => $quotation->gridType,     // Extra info
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $quotations,
                'message' => 'Cotizaciones obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            \Log::error('❌ Error en index cotizaciones: ' . $e->getMessage());
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
        \Log::info("🔍 Consultando cotización ID: {$id}");
        
        try {
            // Usar withTrashed() para detectar si está eliminada lógicamente
            $quotation = Quotation::withTrashed()
                ->with([
                    'client' => function($q) {
                        $q->with('clientType'); // Cargar tipo para mostrar detalles completos
                    },
                    'status', // Cargar estado completo
                    'systemType',
                    'gridType',
                    'quotationProducts',
                    'quotationItems',
                    'user'
                ])->find($id);

            if (!$quotation) {
                \Log::warning("❌ Cotización ID {$id} no encontrada en BD.");
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada o ID inválido'
                ], 404);
            }

            if ($quotation->trashed()) {
                \Log::info("⚠️ Cotización ID {$id} está eliminada (SoftDeleted).");
                // Opcional: Permitir verla pero avisar, o bloquear.
                // Por ahora permitimos verla con una nota o propiedad
                $quotation->is_deleted = true;
            }

            // Agregar información detallada de productos con marca y potencia
            $quotation->quotationProducts->each(function ($quotationProduct) {
                $product = $this->getProduct($quotationProduct->product_type, $quotationProduct->product_id);
                if ($product) {
                    $quotationProduct->product = $product;
                    
                    // Agregar información específica de marca y potencia
                    $quotationProduct->product_brand = $product->brand;
                    $quotationProduct->product_model = $product->model;
                    
                    // Potencia según el tipo de producto
                    switch ($quotationProduct->product_type) {
                        case 'panel':
                            $quotationProduct->product_power = $product->power . ' W';
                            break;
                        case 'inverter':
                            $quotationProduct->product_power = $product->power . ' W';
                            break;
                        case 'battery':
                            $quotationProduct->product_power = $product->capacity . ' Ah / ' . $product->voltage . ' V';
                            break;
                        default:
                            $quotationProduct->product_power = 'N/A';
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


    public function store(Request $request): JsonResponse
    {
        try {
           
            $validator = Validator::make($request->all(), [
                'client_id' => 'required|exists:clients,id',
                'user_id' => 'required|exists:users,id',
                'project_name' => 'required|string|max:200',
                'system_type_id' => 'required|exists:system_types,id',
                'grid_type_id' => 'required|exists:grid_types,id',
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
                'products.*.unit_price_cop' => 'required_with:products|numeric|min:0',
                'products.*.profit_percentage' => 'required_with:products|numeric|min:0|max:1',
                'items' => 'sometimes|array',
                'items.*.description' => 'required_with:items|string|max:500',
                'items.*.category' => 'required_with:items|string|max:50',
                'items.*.quantity' => 'required_with:items|numeric|min:0.01',
                'items.*.unit_measure' => 'required_with:items|string|max:20',
                'items.*.unit_price_cop' => 'required_with:items|numeric|min:0',
                'items.*.profit_percentage' => 'required_with:items|numeric|min:0|max:1',
            ]);

            if ($validator->fails()) {
                \Log::error('❌ Error de validación:', $validator->errors()->toArray());
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $quotationData = $request->only([
                'client_id',
                'user_id',
                'project_name',
                'system_type_id',
                'grid_type_id',
                'power_kwp',
                'panel_count',
                'requires_financing',
                // Campos financieros
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
                'profit_percentage',
                'iva_profit_percentage',
                'commercial_management_percentage',
                'administration_percentage',
                'contingency_percentage',
                'withholding_percentage',
            ]);
            
            // Asignar estado 1 (Borrador) por defecto
            $quotationData['status_id'] = 1;
            
            // Generar código único COT-YYYY-XXXX
            $year = date('Y');
            $lastQuotation = Quotation::whereYear('created_at', $year)
                ->where('code', 'like', "COT-$year-%")
                ->orderBy('id', 'desc')
                ->first();
                
            $sequence = 1;
            if ($lastQuotation && preg_match("/COT-$year-(\d+)/", $lastQuotation->code, $matches)) {
                $sequence = intval($matches[1]) + 1;
            }
            
            $quotationData['code'] = "COT-$year-" . str_pad($sequence, 4, '0', STR_PAD_LEFT);
            \Log::info('🔢 Código generado:', ['code' => $quotationData['code']]);
            
            // Asignar fechas por defecto
            $quotationData['issue_date'] = now();
            $quotationData['expiration_date'] = now()->addDays(15);
            
            \Log::info('💾 Creando cotización con datos:', $quotationData);
            $quotation = Quotation::create($quotationData);
            \Log::info('✅ Cotización creada con ID:', ['id' => $quotation->id]);

            // Crear productos utilizados si se enviaron
            if ($request->has('products')) {
                \Log::info('📦 Procesando ' . count($request->products) . ' productos');
                foreach ($request->products as $index => $productData) {
                    // Obtener información del producto para snapshot
                    $product = $this->getProduct($productData['product_type'], $productData['product_id']);
                    
                    \Log::info("📦 Producto #{$index}:", [
                        'type' => $productData['product_type'],
                        'id' => $productData['product_id'],
                        'quantity' => $productData['quantity'],
                        'unit_price_cop' => $productData['unit_price_cop'] ?? $productData['unit_price'] ?? 0,
                        'product_found' => $product ? 'Sí' : 'No'
                    ]);
                    
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
                \Log::info('✅ Productos creados exitosamente');
            }

            // Crear items si se enviaron
            if ($request->has('items')) {
                \Log::info('📝 Procesando ' . count($request->items) . ' items');
                foreach ($request->items as $index => $itemData) {
                    \Log::info("📝 Item #{$index}:", [
                        'description' => $itemData['description'],
                        'category' => $itemData['category'] ?? $itemData['item_type'] ?? 'material',
                        'quantity' => $itemData['quantity'],
                        'unit_price_cop' => $itemData['unit_price_cop'] ?? $itemData['unit_price'] ?? 0,
                    ]);
                    
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
                \Log::info('✅ Items creados exitosamente');
            }

            // Cargar datos completos para la respuesta
            $quotation->load(['client', 'status', 'quotationProducts', 'quotationItems']);
            
            \Log::info('✅ Cotización creada exitosamente', ['quotation_id' => $quotation->id]);
            \Log::info('=====================================');
            
            return response()->json([
                'success' => true,
                'data' => $quotation,
                'message' => 'Cotización creada exitosamente'
            ], 201);
        } catch (\Exception $e) {
            \Log::error('❌ Error al crear cotización:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
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
                'client_id' => 'sometimes|exists:clients,id',
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
                'status_id' => 'sometimes|exists:quotation_statuses,id',
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
                'used_products.*.used_product_id' => 'sometimes|exists:quotation_products,id',
                'used_products.*.quantity' => 'sometimes|integer|min:1',
                'used_products.*.unit_price' => 'sometimes|numeric|min:0',
                'used_products.*.profit_percentage' => 'sometimes|numeric|min:0|max:1',
                'items' => 'sometimes|array',
                'items.*.item_id' => 'sometimes|exists:quotation_items,id',
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

            // Mapear tipos de sistema y red si vienen como string
            $systemTypeId = $quotation->system_type_id;
            if ($request->filled('system_type')) {
                $systemType = \App\Models\SystemType::where('name', $request->system_type)->first();
                if ($systemType) {
                    $systemTypeId = $systemType->id;
                }
            }

            $gridTypeId = $quotation->grid_type_id;
            if ($request->filled('grid_type')) {
                $gridType = \App\Models\GridType::where('name', $request->grid_type)->first();
                if ($gridType) {
                    $gridTypeId = $gridType->id;
                }
            }

            // Actualizar solo los campos que existen en la base de datos
            $quotation->update([
                'client_id' => $request->client_id ?? $quotation->client_id,
                'user_id' => $request->user_id ?? $quotation->user_id,
                'project_name' => $request->project_name ?? $quotation->project_name,
                'system_type_id' => $systemTypeId,
                'grid_type_id' => $gridTypeId,
                'power_kwp' => $request->power_kwp ?? $quotation->power_kwp,
                'panel_count' => $request->panel_count ?? $quotation->panel_count,
                'requires_financing' => $request->requires_financing ?? $quotation->requires_financing,
                'profit_percentage' => $request->profit_percentage ?? $quotation->profit_percentage,
                'iva_profit_percentage' => $request->iva_profit_percentage ?? $quotation->iva_profit_percentage,
                'commercial_management_percentage' => $request->commercial_management_percentage ?? $quotation->commercial_management_percentage,
                'administration_percentage' => $request->administration_percentage ?? $quotation->administration_percentage,
                'contingency_percentage' => $request->contingency_percentage ?? $quotation->contingency_percentage,
                'withholding_percentage' => $request->withholding_percentage ?? $quotation->withholding_percentage,
                'status_id' => $request->status_id ?? $quotation->status_id,
                // Campos financieros
                'subtotal' => $request->subtotal ?? $quotation->subtotal,
                'profit' => $request->profit ?? $quotation->profit,
                'profit_iva' => $request->profit_iva ?? $quotation->profit_iva,
                'commercial_management' => $request->commercial_management ?? $quotation->commercial_management,
                'administration' => $request->administration ?? $quotation->administration,
                'contingency' => $request->contingency ?? $quotation->contingency,
                'withholdings' => $request->withholdings ?? $quotation->withholdings,
                'total_value' => $request->total_value ?? $quotation->total_value,
                'subtotal2' => $request->subtotal2 ?? $quotation->subtotal2,
                'subtotal3' => $request->subtotal3 ?? $quotation->subtotal3,
            ]);

            // Actualizar productos utilizados si se enviaron
            if ($request->has('used_products')) {
                foreach ($request->used_products as $productData) {
                    if (isset($productData['used_product_id']) && $productData['used_product_id']) {
                        $quotationProduct = QuotationProduct::find($productData['used_product_id']);
                        if ($quotationProduct && $quotationProduct->quotation_id == $quotation->id) { // usar id no quotation_id
                            $quotationProduct->update([
                                'quantity' => $productData['quantity'] ?? $quotationProduct->quantity,
                                'unit_price_cop' => $productData['unit_price'] ?? $quotationProduct->unit_price_cop, // map unit_price -> unit_price_cop
                                'profit_percentage' => $productData['profit_percentage'] ?? $quotationProduct->profit_percentage,
                                // 'partial_value' => se calcula
                                // 'profit' => se calcula
                                // 'total_value' => se calcula
                            ]);
                        }
                    } else {
                        // Crear nuevo producto
                        $quotation->quotationProducts()->create([
                            'product_type' => $productData['product_type'] ?? 'panel',
                            'product_id' => $productData['product_id'] ?? 1,
                            'quantity' => $productData['quantity'] ?? 0,
                            'unit_price_cop' => $productData['unit_price'] ?? 0,
                            'profit_percentage' => $productData['profit_percentage'] ?? 0,
                            'snapshot_brand' => $productData['brand'] ?? 'Genérico',
                            'snapshot_model' => $productData['model'] ?? 'Modelo',
                            'display_order' => 0
                        ]);
                    }
                }
            }

            // Actualizar items si se enviaron
            if ($request->has('items')) {
                foreach ($request->items as $itemData) {
                    if (isset($itemData['item_id']) && $itemData['item_id']) {
                        $item = QuotationItem::find($itemData['item_id']);
                        if ($item && $item->quotation_id == $quotation->id) {
                            $item->update([
                                'description' => $itemData['description'] ?? $item->description,
                                'unit_measure' => $itemData['unit'] ?? $item->unit_measure, // Corrected field name
                                'quantity' => $itemData['quantity'] ?? $item->quantity,
                                'unit_price_cop' => $itemData['unit_price'] ?? $item->unit_price_cop,
                                'profit_percentage' => $itemData['profit_percentage'] ?? $item->profit_percentage,
                            ]);
                        }
                    } else {
                         // Crear nuevo item
                        $quotation->quotationItems()->create([
                            'description' => $itemData['description'] ?? 'Nuevo Item',
                            'quantity' => $itemData['quantity'] ?? 0,
                            'unit_measure' => $itemData['unit'] ?? 'und',
                            'unit_price_cop' => $itemData['unit_price'] ?? 0,
                            'profit_percentage' => $itemData['profit_percentage'] ?? 0,
                            'display_order' => 0
                        ]);
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
                'status_id' => 'required|exists:quotation_statuses,id'
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

            // Crear proyecto automáticamente si el estado cambió a "Aprobada" (ID 3)
            $projectCreated = null;
            if ($oldStatusId != 3 && $request->status_id == 3) {
                $projectCreated = $this->createProjectFromQuotation($quotation);
            }

            $responseData = [
                'quotation_id' => $quotation->id,
                'status' => [
                    'id' => $status->id,
                    'name' => $status->name,
                    'description' => $status->description,
                    'color' => $status->color
                ],
                'updated_at' => $quotation->updated_at
            ];

            // Agregar información del proyecto si se creó
            if ($projectCreated) {
                $responseData['project_created'] = [
                    'project_id' => $projectCreated->id,
                    'project_name' => $projectCreated->name,
                    'status' => $projectCreated->currentState->name ?? 'Sin estado'
                ];
                // Agregar información del centro de costo si se creó
                if (isset($projectCreated->cost_center)) {
                    $responseData['cost_center_created'] = [
                        'cost_center_id' => $projectCreated->cost_center->cost_center_id,
                        'code' => $projectCreated->cost_center->code,
                        'name' => $projectCreated->cost_center->name
                    ];
                }
            }

            $message = 'Estado de cotización actualizado exitosamente';
            if ($projectCreated) {
                $message .= ', proyecto y centro de costo creados automáticamente';
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
     * Obtener especificaciones del producto para snapshot
     */
    private function getProductSpecs($product, $type)
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
     * Crear proyecto automáticamente cuando la cotización se convierte en aprobada
     */
    private function createProjectFromQuotation(Quotation $quotation)
    {
        try {
            \Log::info('=== INICIANDO CREACIÓN DE PROYECTO ===');
            \Log::info('Cotización ID: ' . $quotation->id);
            \Log::info('Cotización Code: ' . $quotation->code);
            \Log::info('Cliente ID: ' . $quotation->client_id);

            // Verificar que no exista ya un proyecto para esta cotización
            $existingProject = Project::where('quotation_id', $quotation->id)->first();
            if ($existingProject) {
                \Log::warning('Ya existe un proyecto para la cotización #' . $quotation->id . ' - Proyecto ID: ' . $existingProject->id);
                return null;
            }

            // Verificar que el cliente existe y tiene los datos necesarios
            if (!$quotation->client) {
                \Log::error('Cliente no encontrado para cotización #' . $quotation->id);
                return null;
            }

            \Log::info('Cliente encontrado - Department ID: ' . $quotation->client->department_id . ', City ID: ' . $quotation->client->city_id);

            // Obtener el estado inicial para el proyecto (Borrador - ID 1)
            $initialState = ProjectState::find(1); // Estado "Borrador"

            if (!$initialState) {
                \Log::error('Estado inicial de proyecto no encontrado (ID 1)');
                \Log::error('Estados disponibles: ' . ProjectState::count());
                return null;
            }

            \Log::info('Estado inicial encontrado: ' . $initialState->name . ' (ID: ' . $initialState->id . ')');

            // Generar código único PRO-YYYY-XXXX para el proyecto
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
            \Log::info('Código de proyecto generado: ' . $projectCode);

            // Determinar tipo de proyecto basado en el tipo de cliente
            // Mapeo: Residencial=1, Comercial=2, Industrial=3, Institucional=4
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
            \Log::info('Tipo de proyecto asignado: ' . $projectTypeId);

            // Preparar datos del proyecto
            $projectData = [
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
            ];

            \Log::info('Datos del proyecto a crear:', $projectData);

            // Crear el proyecto con los campos correctos
            $project = Project::create($projectData);

            \Log::info('Proyecto creado exitosamente con ID: ' . $project->id . ' para cotización #' . $quotation->code);

            // Crear centro de costo asociado al proyecto
            $costCenterCode = 'CC-' . $projectCode;
            $costCenter = CostCenter::create([
                'code' => $costCenterCode,
                'name' => $quotation->project_name,
                'description' => 'Centro de costo para proyecto ' . $projectCode,
                'department_id' => $quotation->client->department_id,
                'project_id' => $project->id,
                'is_active' => true,
            ]);

            \Log::info('Centro de costo creado exitosamente con ID: ' . $costCenter->cost_center_id . ' para proyecto #' . $projectCode);

            // Cargar las relaciones para la respuesta
            $project->load(['currentState']);
            $project->cost_center = $costCenter;

            \Log::info('=== CREACIÓN DE PROYECTO Y CENTRO DE COSTO COMPLETADA ===');

            return $project;

        } catch (\Exception $e) {
            \Log::error('=== ERROR EN CREACIÓN DE PROYECTO ===');
            \Log::error('Cotización ID: ' . $quotation->id);
            \Log::error('Mensaje de error: ' . $e->getMessage());
            \Log::error('Archivo: ' . $e->getFile() . ' - Línea: ' . $e->getLine());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            \Log::error('=== FIN ERROR ===');
            return null;
        }
    }

    /**
     * Obtener datos de cotización para PDF
     * Retorna todos los datos necesarios para generar el PDF en el frontend
     */
    public function downloadPDF($id): JsonResponse
    {
        try {
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
            ])->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            // Preparar datos del cliente
            $clientData = null;
            if ($quotation->client) {
                $clientData = [
                    'id' => $quotation->client->id,
                    'name' => $quotation->client->name,
                    'type' => $quotation->client->clientType?->slug ?? 'residencial',
                    'email' => $quotation->client->email,
                    'phone' => $quotation->client->cellphone ?? $quotation->client->phone,
                    'full_address' => $quotation->client->address,
                    'city' => [
                        'name' => $quotation->client->city?->name ?? 'No disponible'
                    ],
                    'department' => [
                        'name' => $quotation->client->city?->department?->name ?? 'No disponible'
                    ],
                    'monthly_consumption' => $quotation->client->monthly_consumption ?? 0
                ];
            }

            // Transformar productos (suministros)
            $suministros = $quotation->quotationProducts->map(function($product) {
                // Obtener información del producto original
                $productInfo = $this->getProduct($product->product_type, $product->product_id);
                $tipoDisplay = ucfirst($product->product_type);
                
                // Mapear tipos a español
                switch ($product->product_type) {
                    case 'panel':
                        $tipoDisplay = 'Panel Solar';
                        break;
                    case 'inverter':
                        $tipoDisplay = 'Inversor';
                        break;
                    case 'battery':
                        $tipoDisplay = 'Batería';
                        break;
                }

                $cantidad = (float) $product->quantity;
                $precioUnitario = (float) $product->unit_price_cop;
                $porcentajeUtilidad = (float) ($product->profit_percentage * 100);
                $valorParcial = $cantidad * $precioUnitario;
                $utilidad = $valorParcial * $product->profit_percentage;
                $total = $valorParcial + $utilidad;

                return [
                    'tipo' => $tipoDisplay,
                    'descripcion' => ($product->snapshot_brand ?? '') . ' ' . ($product->snapshot_model ?? ''),
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'porcentaje_utilidad' => $porcentajeUtilidad,
                    'valor_parcial' => $valorParcial,
                    'utilidad' => $utilidad,
                    'total' => $total
                ];
            })->toArray();

            // Transformar items complementarios
            $itemsComplementarios = $quotation->quotationItems->map(function($item) {
                $cantidad = (float) $item->quantity;
                $precioUnitario = (float) $item->unit_price_cop;
                $porcentajeUtilidad = (float) ($item->profit_percentage * 100);
                $valorParcial = $cantidad * $precioUnitario;
                $utilidad = $valorParcial * $item->profit_percentage;
                $total = $valorParcial + $utilidad;

                return [
                    'descripcion' => $item->description,
                    'cantidad' => $cantidad,
                    'unidad' => $item->unit_measure ?? 'und',
                    'precio_unitario' => $precioUnitario,
                    'porcentaje_utilidad' => $porcentajeUtilidad,
                    'valor_parcial' => $valorParcial,
                    'utilidad' => $utilidad,
                    'total' => $total
                ];
            })->toArray();

            // Preparar datos para el PDF
            $pdfData = [
                'id' => $quotation->id,
                'numero' => $quotation->code,
                'proyecto' => $quotation->project_name,
                'tipo_sistema' => $quotation->systemType?->name ?? 'On-grid',
                'tipo_red' => $quotation->gridType?->name ?? 'Monofásico',
                'potencia_total' => (float) $quotation->power_kwp,
                'requiere_financing' => (bool) $quotation->requires_financing,
                'cliente' => $clientData,
                'vendedor' => $quotation->user?->name ?? 'No asignado',
                'estado' => $quotation->status?->name ?? 'Borrador',
                'fecha_creacion' => $quotation->created_at?->toIso8601String(),
                'fecha_vencimiento' => $quotation->expiration_date?->toIso8601String(),
                'suministros' => $suministros,
                'items_complementarios' => $itemsComplementarios,
                // Campos financieros
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
                // Porcentajes
                'porcentaje_utilidad' => (float) ($quotation->profit_percentage * 100),
                'porcentaje_gestion_comercial' => (float) ($quotation->commercial_management_percentage * 100),
                'porcentaje_administracion' => (float) ($quotation->administration_percentage * 100),
                'porcentaje_imprevistos' => (float) ($quotation->contingency_percentage * 100),
                'porcentaje_retencion' => (float) ($quotation->withholding_percentage * 100),
            ];

            return response()->json([
                'success' => true,
                'data' => $pdfData,
                'message' => 'Datos de cotización obtenidos para PDF'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener datos para PDF: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener datos para PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de cotizaciones
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalQuotations = Quotation::count();
            $activeQuotations = Quotation::active()->count(); 
            
            $byStatus = \DB::table('quotations')
                ->select('status_id', \DB::raw('count(*) as count'))
                ->whereNull('deleted_at')
                ->groupBy('status_id')
                ->get();
            
            // Map status_id to status name
            $statuses = QuotationStatus::all()->keyBy('id'); 
            
            $formattedStatus = $byStatus->map(function($item) use ($statuses) {
                // Determine the key to lookup. The DB result has status_id.
                $status = $statuses[$item->status_id] ?? null;
                return [
                    'status_id' => $item->status_id,
                    'name' => $status ? $status->name : 'Desconocido',
                    'count' => $item->count,
                    'color' => $status ? $status->color : '#000000'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalQuotations,
                    'active' => $activeQuotations,
                    'by_status' => $formattedStatus
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en estadísticas de cotizaciones: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
