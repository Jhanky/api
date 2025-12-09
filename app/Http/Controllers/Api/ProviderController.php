<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Provider::withCount('invoices')
                ->withSum('invoices', 'total_amount');

            // Búsqueda
            if ($request->has('search') && $request->search) {
                $query->search($request->search);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'provider_name');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = min($request->get('per_page', 15), 100);
            $providers = $query->paginate($perPage);

            // Agregar campos calculados
            $providers->getCollection()->transform(function ($provider) {
                $provider->total_invoiced = $provider->invoices_sum_total_amount ?? '0.00';
                $provider->invoices_count = $provider->invoices_count ?? 0;
                unset($provider->invoices_sum_total_amount);
                return $provider;
            });

            return response()->json([
                'success' => true,
                'message' => 'Proveedores obtenidos exitosamente',
                'data' => $providers
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
                'provider_name' => 'required|string|max:255',
                'NIT' => 'required|string|max:50|unique:providers,NIT'
            ]);

            $provider = Provider::create($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor creado exitosamente',
                'data' => $provider
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
            $provider = Provider::withCount('invoices')
                ->withSum('invoices', 'total_amount')
                ->with(['invoices.costCenter'])
                ->findOrFail($id);

            // Agregar campos calculados
            $provider->total_invoiced = $provider->invoices_sum_total_amount ?? '0.00';
            $provider->invoices_count = $provider->invoices_count ?? 0;
            unset($provider->invoices_sum_total_amount);

            return response()->json([
                'success' => true,
                'message' => 'Proveedor obtenido exitosamente',
                'data' => $provider
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
            $provider = Provider::findOrFail($id);

            $request->validate([
                'provider_name' => 'required|string|max:255',
                'NIT' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('providers', 'NIT')->ignore($provider->provider_id, 'provider_id')
                ]
            ]);

            $provider->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Proveedor actualizado exitosamente',
                'data' => $provider
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
            $provider = Provider::findOrFail($id);

            // Verificar si tiene facturas asociadas
            if ($provider->invoices()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el proveedor porque tiene facturas asociadas'
                ], 400);
            }

            $provider->delete();

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
            $totalProviders = Provider::count();
            $providersWithInvoices = Provider::has('invoices')->count();
            $providersWithoutInvoices = $totalProviders - $providersWithInvoices;
            
            // Obtener total facturado
            $totalInvoiced = Provider::withSum('invoices', 'total_amount')
                ->get()
                ->sum('invoices_sum_total_amount') ?? 0;
            
            $averageInvoicedPerProvider = $providersWithInvoices > 0 ? $totalInvoiced / $providersWithInvoices : 0;
            
            // Top proveedores por monto facturado
            $topProviders = Provider::withSum('invoices', 'total_amount')
                ->withCount('invoices')
                ->having('invoices_sum_total_amount', '>', 0)
                ->orderBy('invoices_sum_total_amount', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($provider) use ($totalInvoiced) {
                    $provider->total_invoiced = $provider->invoices_sum_total_amount ?? '0.00';
                    $provider->percentage = $totalInvoiced > 0 ? 
                        round(($provider->invoices_sum_total_amount / $totalInvoiced) * 100, 2) : 0;
                    unset($provider->invoices_sum_total_amount);
                    return $provider;
                });
            
            // Distribución por cantidad de facturas
            $providersByInvoiceCount = Provider::withCount('invoices')
                ->get()
                ->groupBy('invoices_count')
                ->map->count();

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => [
                    'total_providers' => $totalProviders,
                    'providers_with_invoices' => $providersWithInvoices,
                    'providers_without_invoices' => $providersWithoutInvoices,
                    'total_invoiced' => number_format($totalInvoiced, 2, '.', ''),
                    'average_invoiced_per_provider' => number_format($averageInvoicedPerProvider, 2, '.', ''),
                    'top_providers' => $topProviders,
                    'providers_by_invoice_count' => $providersByInvoiceCount
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

            $query = Provider::withCount('invoices')
                ->withSum('invoices', 'total_amount')
                ->search($request->q);

            $perPage = min($request->get('per_page', 15), 100);
            $providers = $query->paginate($perPage);

            // Agregar campos calculados
            $providers->getCollection()->transform(function ($provider) {
                $provider->total_invoiced = $provider->invoices_sum_total_amount ?? '0.00';
                $provider->invoices_count = $provider->invoices_count ?? 0;
                unset($provider->invoices_sum_total_amount);
                return $provider;
            });

            return response()->json([
                'success' => true,
                'message' => 'Búsqueda realizada exitosamente',
                'data' => $providers
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
            $provider = Provider::findOrFail($id);
            
            $query = $provider->invoices()->with('costCenter');
            
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
                    'provider' => [
                        'provider_id' => $provider->provider_id,
                        'provider_name' => $provider->provider_name,
                        'NIT' => $provider->NIT
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
