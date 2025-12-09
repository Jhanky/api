<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Client::with('user:id,name,email');

            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nic', 'like', "%{$search}%")
                      ->orWhere('address', 'like', "%{$search}%");
                });
            }

            if ($request->has('client_type')) {
                $query->byType($request->get('client_type'));
            }

            if ($request->has('department')) {
                $query->byDepartment($request->get('department'));
            }

            if ($request->has('city')) {
                $query->byCity($request->get('city'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->get('user_id'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $clients = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $clients,
                'message' => 'Clients retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving clients',
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
            $validator = Validator::make($request->all(), [
                'nic' => 'required|string|max:50|unique:clients,nic',
                'client_type' => 'required|string|max:50',
                'name' => 'required|string|max:100',
                'department' => 'required|string|max:100',
                'city' => 'required|string|max:100',
                'address' => 'required|string',
                'monthly_consumption_kwh' => 'required|numeric|min:0',
                'energy_rate' => 'required|numeric|min:0',
                'network_type' => 'required|string|max:50',
                // Remover esta línea: 'user_id' => 'required|exists:users,id',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Agregar el user_id del usuario autenticado
            $data = $request->all();
            $data['user_id'] = Auth::id();

            $client = Client::create($data);
            $client->load('user:id,name,email');

            return response()->json([
                'success' => true,
                'data' => $client,
                'message' => 'Client created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating client',
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
            $client = Client::with('user:id,name,email')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $client,
                'message' => 'Client retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Client not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $client = Client::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'nic' => 'sometimes|required|string|max:50|unique:clients,nic,' . $id . ',client_id',
                'client_type' => 'sometimes|required|string|max:50',
                'name' => 'sometimes|required|string|max:100',
                'department' => 'sometimes|required|string|max:100',
                'city' => 'sometimes|required|string|max:100',
                'address' => 'sometimes|required|string',
                'monthly_consumption_kwh' => 'sometimes|required|numeric|min:0',
                'energy_rate' => 'sometimes|required|numeric|min:0',
                'network_type' => 'sometimes|required|string|max:50',
                'user_id' => 'sometimes|required|exists:users,id',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $client->update($request->all());
            $client->load('user:id,name,email');

            return response()->json([
                'success' => true,
                'data' => $client,
                'message' => 'Client updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating client',
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
            $client = Client::findOrFail($id);
            $client->delete();

            return response()->json([
                'success' => true,
                'message' => 'Client deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting client',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get clients by user
     */
    public function getByUser(string $userId): JsonResponse
    {
        try {
            $clients = Client::where('user_id', $userId)
                           ->active()
                           ->with('user:id,name,email')
                           ->get();

            return response()->json([
                'success' => true,
                'data' => $clients,
                'message' => 'User clients retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving user clients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get client statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_clients' => Client::count(),
                'active_clients' => Client::active()->count(),
                'inactive_clients' => Client::where('is_active', false)->count(),
                'clients_by_type' => Client::selectRaw('client_type, COUNT(*) as count')
                                          ->groupBy('client_type')
                                          ->get(),
                'clients_by_department' => Client::selectRaw('department, COUNT(*) as count')
                                                ->groupBy('department')
                                                ->orderBy('count', 'desc')
                                                ->limit(10)
                                                ->get(),
                'total_consumption' => Client::sum('monthly_consumption_kwh'),
                'average_consumption' => Client::avg('monthly_consumption_kwh'),
                'average_energy_rate' => Client::avg('energy_rate')
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Client statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
