<?php

namespace App\Http\Controllers\Apk;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ClientController extends Controller
{
    /**
     * Listar clientes para móvil (versión simplificada)
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Client::with('user:id,name,email');

            // Filtros básicos para móvil
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nic', 'like', "%{$search}%");
                });
            }

            if ($request->has('client_type')) {
                $query->where('client_type', $request->get('client_type'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Paginación optimizada para móvil
            $perPage = min($request->get('per_page', 15), 50);
            $clients = $query->orderBy('updated_at', 'desc')->paginate($perPage);

            $transformedClients = $clients->map(function ($client) {
                return [
                    'id' => $client->client_id,
                    'name' => $client->name,
                    'nic' => $client->nic,
                    'type' => $client->client_type,
                    'location' => $client->city . ', ' . $client->department,
                    'is_active' => $client->is_active,
                    'monthly_consumption' => $client->monthly_consumption_kwh,
                    'energy_rate' => $client->energy_rate,
                    'created_at' => $client->created_at->toISOString(),
                    'updated_at' => $client->updated_at->toISOString()
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'clients' => $transformedClients,
                    'pagination' => [
                        'current_page' => $clients->currentPage(),
                        'last_page' => $clients->lastPage(),
                        'per_page' => $clients->perPage(),
                        'total' => $clients->total(),
                        'has_more' => $clients->hasMorePages()
                    ]
                ],
                'message' => 'Clientes obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los clientes: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener cliente específico para móvil
     * 
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $client = Client::with('user:id,name,email')->find($id);

            if (!$client) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cliente no encontrado'
                ], 404);
            }

            $clientData = [
                'id' => $client->client_id,
                'name' => $client->name,
                'nic' => $client->nic,
                'type' => $client->client_type,
                'location' => [
                    'department' => $client->department,
                    'city' => $client->city,
                    'address' => $client->address,
                    'full_location' => $client->city . ', ' . $client->department
                ],
                'energy_info' => [
                    'monthly_consumption_kwh' => $client->monthly_consumption_kwh,
                    'energy_rate' => $client->energy_rate,
                    'network_type' => $client->network_type,
                    'estimated_monthly_cost' => $client->monthly_consumption_kwh * $client->energy_rate
                ],
                'user' => $client->user ? [
                    'name' => $client->user->name,
                    'email' => $client->user->email
                ] : null,
                'is_active' => $client->is_active,
                'created_at' => $client->created_at->toISOString(),
                'updated_at' => $client->updated_at->toISOString()
            ];

            return response()->json([
                'success' => true,
                'data' => $clientData,
                'message' => 'Cliente obtenido exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas básicas de clientes para móvil
     * 
     * @return JsonResponse
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalClients = Client::count();
            $activeClients = Client::where('is_active', true)->count();
            $residentialClients = Client::where('client_type', 'Residencial')->count();
            $commercialClients = Client::where('client_type', 'Comercial')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_clients' => $totalClients,
                    'active_clients' => $activeClients,
                    'inactive_clients' => $totalClients - $activeClients,
                    'residential_clients' => $residentialClients,
                    'commercial_clients' => $commercialClients,
                    'other_clients' => $totalClients - $residentialClients - $commercialClients
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }
}
