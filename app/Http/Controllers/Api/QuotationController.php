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
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    /**
     * Obtener todas las cotizaciones
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Quotation::with([
                'client:client_id,nic,client_type,name,department,city,address,monthly_consumption_kwh,energy_rate,network_type',
                'user:id,name',
                'status:status_id,name,description',
                'usedProducts',
                'items'
            ]);

            // Filtros
            if ($request->has('status_id')) {
                $query->byStatus($request->status_id);
            }

            if ($request->has('client_id')) {
                $query->byClient($request->client_id);
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

            $quotations = $query->orderBy('creation_date', 'desc')
                               ->paginate($request->get('per_page', 15));

            return response()->json($quotations);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener cotizaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una nueva cotización
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,client_id',
            'user_id' => 'required|exists:users,id',
            'project_name' => 'required|string|max:255',
            'system_type' => 'required|in:Interconectado,Aislado,Híbrido',
            'power_kwp' => 'required|numeric|min:0',
            'panel_count' => 'required|integer|min:1',
            'requires_financing' => 'required|boolean',
            'profit_percentage' => 'required|numeric|min:0|max:1',
            'iva_profit_percentage' => 'required|numeric|min:0|max:1',
            'commercial_management_percentage' => 'required|numeric|min:0|max:1',
            'administration_percentage' => 'required|numeric|min:0|max:1',
            'contingency_percentage' => 'required|numeric|min:0|max:1',
            'withholding_percentage' => 'required|numeric|min:0|max:1',
            'products' => 'required|array|min:1',
            'products.*.product_type' => 'required|in:panel,inverter,battery',
            'products.*.product_id' => 'required|integer',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'products.*.profit_percentage' => 'required|numeric|min:0|max:1',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.item_type' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:0',
            'items.*.unit' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.profit_percentage' => 'required|numeric|min:0|max:1',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Validar que el cliente y usuario existan
            $client = Client::find($request->client_id);
            $user = User::find($request->user_id);

            if (!$client || !$user) {
                return response()->json([
                    'message' => 'Cliente o usuario no encontrado'
                ], 404);
            }

            // Validar y calcular productos
            $validProducts = [];
            $subtotalProducts = 0;

            foreach ($request->products as $productData) {
                // Validar que el producto existe
                $product = $this->getProduct($productData['product_type'], $productData['product_id']);
                if (!$product) {
                    throw new \Exception("Producto {$productData['product_type']} con ID {$productData['product_id']} no encontrado");
                }

                // Calcular valores
                $partialValue = $productData['quantity'] * $productData['unit_price'];
                $profit = $partialValue * $productData['profit_percentage'];
                $totalValue = $partialValue + $profit;

                $validProducts[] = [
                    'product_type' => $productData['product_type'],
                    'product_id' => $productData['product_id'],
                    'quantity' => $productData['quantity'],
                    'unit_price' => $productData['unit_price'],
                    'partial_value' => $partialValue,
                    'profit_percentage' => $productData['profit_percentage'],
                    'profit' => $profit,
                    'total_value' => $totalValue
                ];

                $subtotalProducts += $totalValue;
            }

            // Validar y calcular items
            $validItems = [];
            $subtotalItems = 0;

            foreach ($request->items as $itemData) {
                $partialValue = $itemData['quantity'] * $itemData['unit_price'];
                $profit = $partialValue * $itemData['profit_percentage'];
                $totalValue = $partialValue + $profit;

                $validItems[] = [
                    'descripcion' => $itemData['description'],
                    'tipo_item' => $itemData['item_type'],
                    'cantidad' => $itemData['quantity'],
                    'unidad' => $itemData['unit'],
                    'precio_unitario' => $itemData['unit_price'],
                    'valor_parcial' => $partialValue,
                    'porcentaje_ganancia' => $itemData['profit_percentage'],
                    'ganancia' => $profit,
                    'valor_total_item' => $totalValue
                ];

                $subtotalItems += $totalValue;
            }

            // Calcular totales de la cotización
            $subtotal = $subtotalProducts + $subtotalItems;
            $commercialManagement = $subtotal * $request->commercial_management_percentage;
            $subtotal2 = $subtotal + $commercialManagement;
            
            $administrative = $subtotal2 * $request->administration_percentage;
            $contingency = $subtotal2 * $request->contingency_percentage;
            $profit = $subtotal2 * $request->profit_percentage;
            $ivaProfit = $profit * $request->iva_profit_percentage;
            
            $subtotal3 = $subtotal2 + $administrative + $contingency + $profit + $ivaProfit;
            $withholdings = $subtotal3 * $request->withholding_percentage;
            $totalValue = $subtotal3 + $withholdings;

            // Crear la cotización
            $quotation = Quotation::create([
                'client_id' => $request->client_id,
                'user_id' => $request->user_id,
                'project_name' => $request->project_name,
                'system_type' => $request->system_type,
                'power_kwp' => $request->power_kwp,
                'panel_count' => $request->panel_count,
                'requires_financing' => $request->requires_financing,
                'profit_percentage' => $request->profit_percentage,
                'iva_profit_percentage' => $request->iva_profit_percentage,
                'commercial_management_percentage' => $request->commercial_management_percentage,
                'administration_percentage' => $request->administration_percentage,
                'contingency_percentage' => $request->contingency_percentage,
                'withholding_percentage' => $request->withholding_percentage,
                'subtotal' => $subtotal,
                'subtotal2' => $subtotal2,
                'subtotal3' => $subtotal3,
                'profit' => $profit,
                'profit_iva' => $ivaProfit,
                'commercial_management' => $commercialManagement,
                'administration' => $administrative,
                'contingency' => $contingency,
                'withholdings' => $withholdings,
                'total_value' => $totalValue,
                'status_id' => 1 // Estado inicial
            ]);

            // Crear productos utilizados
            foreach ($validProducts as $productData) {
                $productData['quotation_id'] = $quotation->quotation_id;
                UsedProduct::create($productData);
            }

            // Crear items de cotización
            foreach ($validItems as $itemData) {
                $itemData['id_cotizacion'] = $quotation->quotation_id;
                ItemCotizacion::create($itemData);
            }

            DB::commit();

            // Retornar la cotización creada con sus relaciones
            $completeQuotation = Quotation::with([
                'client',
                'user',
                'status',
                'usedProducts',
                'items'
            ])->find($quotation->quotation_id);

            return response()->json([
                'message' => 'Cotización creada exitosamente',
                'data' => $completeQuotation
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Error al crear cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una cotización específica
     */
    public function show($id): JsonResponse
    {
        try {
            $quotation = Quotation::with([
                'client',
                'user',
                'status',
                'usedProducts',
                'items'
            ])->find($id);

            if (!$quotation) {
                return response()->json([
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            return response()->json($quotation);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar una cotización (principalmente el estado)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:quotation_statuses,status_id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $quotation = Quotation::find($id);
            if (!$quotation) {
                return response()->json([
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            $quotation->update(['status_id' => $request->status_id]);

            // Si el estado es 'aprobada', crear proyecto automáticamente
            $status = QuotationStatus::find($request->status_id);
            if ($status && strtolower($status->name) === 'aprobada') {
                // Aquí puedes agregar la lógica para crear el proyecto
                // Project::create([...]);
            }

            return response()->json([
                'message' => 'Cotización actualizada exitosamente',
                'data' => $quotation->load(['client', 'user', 'status'])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar cotización',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una cotización
     */
    public function destroy($id): JsonResponse
    {
        try {
            $quotation = Quotation::find($id);
            if (!$quotation) {
                return response()->json([
                    'message' => 'Cotización no encontrada'
                ], 404);
            }

            // Las relaciones se eliminan automáticamente por cascade
            $quotation->delete();

            return response()->json([
                'message' => 'Cotización eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar cotización',
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
}