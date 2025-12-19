<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Supplier::withCount('invoices')
                ->withSum('invoices', 'total_value');

            // Búsqueda
            if ($request->has('search') && $request->search) {
                $searchTerm = $request->search;
                $query->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('nit', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('contact_person', 'like', "%{$searchTerm}%");
                });
            }

            // Filtro por estado
            if ($request->has('is_active') && $request->is_active !== 'all') {
                $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $suppliers = $query->with('city.department')->paginate($perPage);

            // Agregar campos calculados
            $suppliers->getCollection()->transform(function ($supplier) {
                $supplier->total_invoiced = $supplier->invoices_sum_total_value ?? 0;
                $supplier->invoices_count = $supplier->invoices_count ?? 0;
                unset($supplier->invoices_sum_total_value);
                return $supplier;
            });

            return response()->json([
                'success' => true,
                'message' => 'Proveedores obtenidos exitosamente',
                'data' => $suppliers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedores',
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
                'nit' => 'required|string|max:50|unique:suppliers,nit',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:255',
                'city_id' => 'nullable|integer|exists:cities,city_id',
                'contact_person' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            $supplier = Supplier::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'data' => $supplier
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
                'message' => 'Error al crear proveedor',
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
            $supplier = Supplier::withCount('invoices')
                ->withSum('invoices', 'total_value')
                ->with(['invoices.costCenter', 'city.department'])
                ->findOrFail($id);

            // Agregar campos calculados
            $supplier->total_invoiced = $supplier->invoices_sum_total_value ?? 0;
            $supplier->invoices_count = $supplier->invoices_count ?? 0;
            unset($supplier->invoices_sum_total_value);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor obtenido exitosamente',
                'data' => $supplier
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener proveedor',
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
            $supplier = Supplier::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255',
                'nit' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('suppliers', 'nit')->ignore($supplier->supplier_id, 'supplier_id')
                ],
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'address' => 'nullable|string|max:255',
                'city_id' => 'nullable|integer|exists:cities,city_id',
                'contact_person' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            $supplier->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente',
                'data' => $supplier
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
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
                'message' => 'Error al actualizar proveedor',
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
            $supplier = Supplier::findOrFail($id);

            // Verificar si tiene facturas asociadas
            if ($supplier->invoices()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proveedor porque tiene facturas asociadas'
                ], 400);
            }

            $supplier->delete();

            return response()->json([
                'success' => true,
                'message' => 'Proveedor eliminado exitosamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de proveedores
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalSuppliers = Supplier::count();
            $activeSuppliers = Supplier::where('is_active', true)->count();
            $inactiveSuppliers = $totalSuppliers - $activeSuppliers;
            $suppliersWithInvoices = Supplier::has('invoices')->count();
            
            // Obtener total facturado
            $totalInvoiced = Supplier::withSum('invoices', 'total_value')
                ->get()
                ->sum('invoices_sum_total_value') ?? 0;
            
            // Pagos pendientes (facturas no pagadas)
            $totalPending = Supplier::with(['invoices' => function($q) {
                $q->where('status', '!=', 'pagada');
            }])->get()->sum(function($supplier) {
                return $supplier->invoices->sum('total_value');
            });
            
            // Top proveedores por monto facturado
            $topSuppliers = Supplier::withSum('invoices', 'total_value')
                ->withCount('invoices')
                ->having('invoices_sum_total_value', '>', 0)
                ->orderBy('invoices_sum_total_value', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($supplier) use ($totalInvoiced) {
                    $supplier->total_invoiced = $supplier->invoices_sum_total_value ?? 0;
                    $supplier->percentage = $totalInvoiced > 0 ? 
                        round(($supplier->invoices_sum_total_value / $totalInvoiced) * 100, 2) : 0;
                    unset($supplier->invoices_sum_total_value);
                    return $supplier;
                });

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => [
                    'total_suppliers' => $totalSuppliers,
                    'active_suppliers' => $activeSuppliers,
                    'inactive_suppliers' => $inactiveSuppliers,
                    'suppliers_with_invoices' => $suppliersWithInvoices,
                    'total_invoiced' => $totalInvoiced,
                    'total_pending' => $totalPending,
                    'top_suppliers' => $topSuppliers
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
     * Buscar proveedores
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'q' => 'required|string|min:1'
            ]);

            $searchTerm = $request->q;
            $query = Supplier::withCount('invoices')
                ->withSum('invoices', 'total_value')
                ->where(function($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('nit', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%");
                });

            $perPage = min($request->get('per_page', 15), 100);
            $suppliers = $query->paginate($perPage);

            // Agregar campos calculados
            $suppliers->getCollection()->transform(function ($supplier) {
                $supplier->total_invoiced = $supplier->invoices_sum_total_value ?? 0;
                $supplier->invoices_count = $supplier->invoices_count ?? 0;
                unset($supplier->invoices_sum_total_value);
                return $supplier;
            });

            return response()->json([
                'success' => true,
                'message' => 'Búsqueda realizada exitosamente',
                'data' => $suppliers
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
                'message' => 'Error al buscar proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener facturas de un proveedor específico
     */
    public function invoices(Request $request, string $id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            $query = $supplier->invoices()->with('costCenter');
            
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
                'message' => 'Facturas del proveedor obtenidas exitosamente',
                'data' => [
                    'supplier' => [
                        'supplier_id' => $supplier->supplier_id,
                        'name' => $supplier->name,
                        'nit' => $supplier->nit
                    ],
                    'invoices' => $invoices
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Proveedor no encontrado'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas del proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
