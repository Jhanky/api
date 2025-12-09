<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\UsedProduct;
use App\Models\ItemCotizacion;
use App\Models\Client;
use App\Models\Location;
use App\Models\QuotationStatus;
use App\Models\Panel;
use App\Models\Inverter;
use App\Models\Battery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatAIController extends Controller
{
    /**
     * 1. CREAR - Crear cotización desde IA/Chat sin autenticación
     * Usa los modelos normales de cotizaciones pero con cliente simplificado
     */
    public function create(Request $request): JsonResponse
    {
        try {
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                // Datos mínimos del cliente
                'client_name' => 'required|string|max:255',
                
                // Datos de ubicación
                'location_department' => 'required|string|max:100',
                'location_municipality' => 'required|string|max:100',
                'location_radiation' => 'required|numeric|min:0|max:10',
                
                // Datos de la cotización
                'project_name' => 'required|string|max:255',
                'system_type' => 'required|in:On-grid,Off-grid,Híbrido',
                'power_kwp' => 'required|numeric|min:0.1|max:1000',
                'panel_count' => 'required|integer|min:1|max:10000',
                
                // Productos utilizados (obligatorios: panel e inversor)
                'products' => 'required|array|min:2',
                'products.*.product_type' => 'required|in:panel,inverter,battery',
                'products.*.product_id' => 'required|integer|min:1',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.unit_price' => 'required|numeric|min:0' // Precio SIN utilidad
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            try {
                // 1. Crear cliente simplificado
                $client = $this->createSimplifiedClient($request->client_name, $request->location_department, $request->location_municipality);
                
                // 2. Crear ubicación
                $location = $this->createLocation($request->location_department, $request->location_municipality, $request->location_radiation);
                
                // 3. Crear cotización normal
                $quotation = Quotation::create([
                    'client_id' => $client->client_id,
                    'user_id' => null, // Sin usuario (creada por IA)
                    'project_name' => $request->project_name,
                    'system_type' => $request->system_type,
                    'power_kwp' => $request->power_kwp,
                    'panel_count' => $request->panel_count,
                    'requires_financing' => false,
                    'profit_percentage' => 0.25, // 25% por defecto
                    'iva_profit_percentage' => 0.19, // 19% IVA
                    'commercial_management_percentage' => 0.05, // 5% gestión comercial
                    'administration_percentage' => 0.03, // 3% administración
                    'contingency_percentage' => 0.02, // 2% contingencia
                    'withholding_percentage' => 0.035, // 3.5% retenciones
                    'status_id' => 1 // Pendiente
                ]);

                // 4. Crear productos con utilidad aplicada automáticamente
                $totalProductsValue = 0;
                if ($request->has('products') && is_array($request->products)) {
                    foreach ($request->products as $productData) {
                        $unitPriceWithoutUtility = $productData['unit_price'];
                        $unitPriceWithUtility = $unitPriceWithoutUtility * 1.25; // Aplicar 25% utilidad
                        $partialValue = $productData['quantity'] * $unitPriceWithUtility;
                        $profit = $partialValue * 0.25; // 25% de utilidad
                        $totalValue = $partialValue + $profit;
                        
                        UsedProduct::create([
                            'quotation_id' => $quotation->quotation_id,
                            'product_type' => $productData['product_type'],
                            'product_id' => $productData['product_id'],
                            'quantity' => $productData['quantity'],
                            'unit_price' => $unitPriceWithUtility,
                            'partial_value' => $partialValue,
                            'profit_percentage' => 25.00,
                            'profit' => $profit,
                            'total_value' => $totalValue
                        ]);
                        
                        $totalProductsValue += $totalValue;
                    }
                }

                // 5. Crear items estándar automáticamente
                $this->createStandardItems($quotation->quotation_id, $request->power_kwp, $request->panel_count);

                // 6. Calcular totales de la cotización
                $quotation->calculateTotals();

                DB::commit();

                // Cargar relaciones para la respuesta
                $quotation->load(['client', 'status', 'usedProducts', 'items']);

                return response()->json([
                    'success' => true,
                    'message' => 'Cotización creada exitosamente desde IA',
                    'data' => [
                        'quotation' => $quotation,
                        'quotation_id' => $quotation->quotation_id,
                        'client_id' => $client->client_id,
                        'location_id' => $location->location_id,
                        'total_products' => count($request->products),
                        'total_items' => 5, // Items estándar automáticos
                        'created_at' => $quotation->created_at->format('Y-m-d H:i:s')
                    ]
                ], 201);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear cotización desde IA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear cliente simplificado para IA
     */
    private function createSimplifiedClient($clientName, $department, $city)
    {
        // Generar NIC único para IA
        $nic = 'IA_' . time() . '_' . rand(1000, 9999);
        
        return Client::create([
            'nic' => $nic,
            'client_type' => 'natural',
            'name' => $clientName,
            'department' => $department,
            'city' => $city,
            'address' => 'Dirección no especificada',
            'monthly_consumption_kwh' => 0,
            'energy_rate' => 0,
            'network_type' => 'monofasico',
            'user_id' => null, // Sin usuario (creado por IA)
            'is_active' => true
        ]);
    }

    /**
     * Crear ubicación
     */
    private function createLocation($department, $municipality, $radiation)
    {
        return Location::create([
            'department' => $department,
            'municipality' => $municipality,
            'radiation' => $radiation
        ]);
    }

    /**
     * Crear los 5 items estándar automáticamente
     */
    private function createStandardItems($quotationId, $powerKwp, $panelCount)
    {
        $standardItems = [
            [
                'description' => 'Conductor fotovoltaico',
                'item_type' => 'conductor_fotovoltaico',
                'quantity' => (int)($powerKwp * 12), // power_kwp * 12
                'unit' => 'metro',
                'unit_price' => 4047, // Valor fijo
                'profit_percentage' => 0.25
            ],
            [
                'description' => 'Cableado fotovoltaico',
                'item_type' => 'material_electrico',
                'quantity' => $powerKwp, // Igual al número de paneles
                'unit' => 'metros',
                'unit_price' => 230000, // Valor fijo
                'profit_percentage' => 0.25
            ],
            [
                'description' => 'Estructura de soporte',
                'item_type' => 'estructura',
                'quantity' => $panelCount, // Igual al número de paneles
                'unit' => 'kit',
                'unit_price' => 120000, // Valor fijo
                'profit_percentage' => 0.25
            ],
            [
                'description' => 'Mano de obra instalación',
                'item_type' => 'mano_obra',
                'quantity' => $panelCount, // Igual al número de paneles
                'unit' => 'servicio',
                'unit_price' => 200000, // Valor fijo
                'profit_percentage' => 0.25
            ],
            [
                'description' => 'Costo de legalización',
                'item_type' => 'legalization',
                'quantity' => 1, // Siempre 1
                'unit' => 'servicio',
                'unit_price' => 7500000, // Valor fijo
                'profit_percentage' => 0.05
            ]
        ];

        foreach ($standardItems as $itemData) {
            // Calcular precio total con ganancia
            $partialValue = $itemData['quantity'] * $itemData['unit_price'];
            $profit = $partialValue * $itemData['profit_percentage'];
            $totalValue = $partialValue + $profit;

            ItemCotizacion::create([
                'quotation_id' => $quotationId,
                'description' => $itemData['description'],
                'item_type' => $itemData['item_type'],
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'],
                'unit_price' => $itemData['unit_price'],
                'partial_value' => $partialValue,
                'profit_percentage' => $itemData['profit_percentage'] * 100, // Convertir a porcentaje
                'profit' => $profit,
                'total_value' => $totalValue
            ]);
        }
    }

    /**
     * Obtener información del producto del catálogo
     */
    private function getCatalogProduct($productType, $productId)
    {
        try {
            switch ($productType) {
                case 'panel':
                    $product = Panel::find($productId);
                    return [
                        'brand' => $product->brand ?? null,
                        'model' => $product->model ?? null,
                        'specifications' => $product->power . ' ' . $product->type ?? null
                    ];
                case 'inverter':
                    $product = Inverter::find($productId);
                    return [
                        'brand' => $product->brand ?? null,
                        'model' => $product->model ?? null,
                        'specifications' => $product->power . ' ' . $product->type ?? null
                    ];
                case 'battery':
                    $product = Battery::find($productId);
                    return [
                        'brand' => $product->brand ?? null,
                        'model' => $product->model ?? null,
                        'specifications' => $product->capacity . ' ' . $product->voltage ?? null
                    ];
                default:
                    return [];
            }
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * 2. LISTAR - Obtener cotizaciones creadas por IA
     */
    public function list(): JsonResponse
    {
        try {
            $quotations = Quotation::with(['client', 'status', 'usedProducts', 'items'])
                ->whereNull('user_id') // Cotizaciones creadas por IA (sin usuario)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $quotations
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cotizaciones de IA',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 3. ELIMINAR - Eliminar cotización creada por IA
     */
    public function delete($id): JsonResponse
    {
        try {
            $quotation = Quotation::whereNull('user_id') // Solo cotizaciones de IA
                ->find($id);

            if (!$quotation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cotización no encontrada o no es de IA'
                ], 404);
            }

            // Eliminar en cascada (productos, items, etc.)
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
     * 4. INFORMACIÓN - Obtener información de productos disponibles para IA
     */
    public function info(): JsonResponse
    {
        try {
            $products = [
                'panels' => Panel::select('panel_id', 'brand', 'model', 'power', 'type', 'price')
                    ->orderBy('brand')
                    ->orderBy('model')
                    ->get(),
                'inverters' => Inverter::select('inverter_id', 'brand', 'model', 'power', 'system_type', 'price')
                    ->orderBy('brand')
                    ->orderBy('model')
                    ->get(),
                'batteries' => Battery::select('battery_id', 'brand', 'model', 'capacity', 'voltage', 'price')
                    ->orderBy('brand')
                    ->orderBy('model')
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'data' => $products
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
