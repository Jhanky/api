<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CostCenterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = CostCenter::withCount('invoices')
                ->withSum('invoices', 'total_amount');

            // Búsqueda
            if ($request->has('search') && $request->search) {
                $query->search($request->search);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $costCenters = $query->paginate($perPage);

            // Agregar campos calculados
            $costCenters->getCollection()->transform(function ($costCenter) {
                $costCenter->total_invoiced = $costCenter->invoices_sum_total_amount ?? '0.00';
                $costCenter->invoices_count = $costCenter->invoices_count ?? 0;
                unset($costCenter->invoices_sum_total_amount);
                return $costCenter;
            });

            return response()->json([
                'success' => true,
                'message' => 'Centros de costo obtenidos exitosamente',
                'data' => $costCenters
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener centros de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:20|unique:cost_centers,code'
            ]);

            $costCenter = CostCenter::create([
                'name' => $request->name,
                'code' => $request->code,
                'description' => $request->description,
                'department_id' => $request->department_id,
                'is_active' => $request->is_active ?? true
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Centro de costo creado exitosamente',
                'data' => $costCenter
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $costCenter = CostCenter::withCount('invoices')
                ->withSum('invoices', 'total_amount')
                ->with(['invoices.provider'])
                ->findOrFail($id);

            // Agregar campos calculados
            $costCenter->total_invoiced = $costCenter->invoices_sum_total_amount ?? '0.00';
            $costCenter->invoices_count = $costCenter->invoices_count ?? 0;
            unset($costCenter->invoices_sum_total_amount);

            return response()->json([
                'success' => true,
                'message' => 'Centro de costo obtenido exitosamente',
                'data' => $costCenter
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de costo no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $costCenter = CostCenter::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'nullable|string|max:20'
            ]);

            $costCenter->update([
                'name' => $request->name ?? $costCenter->name,
                'code' => $request->code ?? $costCenter->code,
                'description' => $request->description ?? $costCenter->description,
                'department_id' => $request->department_id ?? $costCenter->department_id,
                'is_active' => $request->is_active ?? $costCenter->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Centro de costo actualizado exitosamente',
                'data' => $costCenter
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de costo no encontrado'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $costCenter = CostCenter::findOrFail($id);

            // Verificar si tiene facturas asociadas
            if ($costCenter->invoices()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el centro de costo porque tiene facturas asociadas'
                ], 400);
            }

            $costCenter->delete();

            return response()->json([
                'success' => true,
                'message' => 'Centro de costo eliminado exitosamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de costo no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de centros de costo
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalCostCenters = CostCenter::count();
            $costCentersWithInvoices = CostCenter::has('invoices')->count();
            $costCentersWithoutInvoices = $totalCostCenters - $costCentersWithInvoices;
            
            // Obtener total facturado
            $totalInvoiced = CostCenter::withSum('invoices', 'total_amount')
                ->get()
                ->sum('invoices_sum_total_amount') ?? 0;
            
            $averageInvoicedPerCostCenter = $costCentersWithInvoices > 0 ? $totalInvoiced / $costCentersWithInvoices : 0;
            
            // Top centros de costo por monto facturado
            $topCostCenters = CostCenter::withSum('invoices', 'total_amount')
                ->withCount('invoices')
                ->having('invoices_sum_total_amount', '>', 0)
                ->orderBy('invoices_sum_total_amount', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($costCenter) use ($totalInvoiced) {
                    $costCenter->total_invoiced = $costCenter->invoices_sum_total_amount ?? '0.00';
                    $costCenter->percentage = $totalInvoiced > 0 ? 
                        round(($costCenter->invoices_sum_total_amount / $totalInvoiced) * 100, 2) : 0;
                    unset($costCenter->invoices_sum_total_amount);
                    return $costCenter;
                });
            
            // Distribución por cantidad de facturas
            $costCentersByInvoiceCount = CostCenter::withCount('invoices')
                ->get()
                ->groupBy('invoices_count')
                ->map->count();

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => [
                    'total_cost_centers' => $totalCostCenters,
                    'cost_centers_with_invoices' => $costCentersWithInvoices,
                    'cost_centers_without_invoices' => $costCentersWithoutInvoices,
                    'total_invoiced' => number_format($totalInvoiced, 2, '.', ''),
                    'average_invoiced_per_cost_center' => number_format($averageInvoicedPerCostCenter, 2, '.', ''),
                    'top_cost_centers' => $topCostCenters,
                    'cost_centers_by_invoice_count' => $costCentersByInvoiceCount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar centros de costo
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1'
            ]);

            $query = CostCenter::withCount('invoices')
                ->withSum('invoices', 'total_amount')
                ->search($request->q);

            $perPage = min($request->get('per_page', 15), 100);
            $costCenters = $query->paginate($perPage);

            // Agregar campos calculados
            $costCenters->getCollection()->transform(function ($costCenter) {
                $costCenter->total_invoiced = $costCenter->invoices_sum_total_amount ?? '0.00';
                $costCenter->invoices_count = $costCenter->invoices_count ?? 0;
                unset($costCenter->invoices_sum_total_amount);
                return $costCenter;
            });

            return response()->json([
                'success' => true,
                'message' => 'Búsqueda realizada exitosamente',
                'data' => $costCenters
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar centros de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener facturas de un centro de costo específico
     */
    public function invoices(Request $request, string $id): JsonResponse
    {
        try {
            $costCenter = CostCenter::findOrFail($id);
            
            $query = $costCenter->invoices()->with('provider');
            
            // Filtros
            if ($request->has('status') && $request->status) {
                $query->where('status', $request->status);
            }
            
            // Ordenamiento
            $sortBy = $request->get('sort_by', 'invoice_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);
            
            $perPage = min($request->get('per_page', 15), 100);
            $invoices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Facturas del centro de costo obtenidas exitosamente',
                'data' => [
                    'cost_center' => [
                        'cost_center_id' => $costCenter->cost_center_id,
                        'name' => $costCenter->name,
                        'code' => $costCenter->code
                    ],
                    'invoices' => $invoices
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Centro de costo no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas del centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
