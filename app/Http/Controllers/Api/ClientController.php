<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Services\ClientService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{
    use ApiResponseTrait;

    protected ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'client_type_id', 'department_id', 'city_id',
                'is_active', 'responsible_user_id', 'min_consumption',
                'max_consumption', 'sort_by', 'sort_order'
            ]);

            $perPage = $request->get('per_page', 25);
            $clients = $this->clientService->getClients($filters, $perPage);

            return $this->paginationResponse(
                $clients,
                'Clientes obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los clientes');
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
                'client_type_id' => 'required|exists:client_types,id',
                'name' => 'required|string|max:100',
                'document_type' => 'required|string|max:20',
                'document_number' => 'required|string|max:50|unique:clients,document_number',
                'email' => 'nullable|email|max:100',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'department_id' => 'required|exists:departments,id',
                'city_id' => 'required|exists:cities,id',
                'address' => 'required|string',
                'monthly_consumption_kwh' => 'required|numeric|min:0',
                'tariff_cop_kwh' => 'required|numeric|min:0',
                'responsible_user_id' => 'nullable|exists:users,id',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
                'primary_contact' => 'nullable|array',
                'primary_contact.name' => 'required_with:primary_contact|string|max:100',
                'primary_contact.email' => 'nullable|email|max:100',
                'primary_contact.phone' => 'nullable|string|max:20',
                'primary_contact.position' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }

            $data = $request->all();
            $data['responsible_user_id'] = $data['responsible_user_id'] ?? Auth::id();

            $client = $this->clientService->createClient($data);

            return $this->createdResponse($client, 'Cliente creado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear el cliente');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $client = $this->clientService->getClientById((int) $id);

            return $this->successResponse($client, 'Cliente obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Cliente no encontrado');
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
                'nic' => 'sometimes|required|string|max:50|unique:clients,nic,' . $id,
                'client_type_id' => 'sometimes|required|exists:client_types,id',
                'name' => 'sometimes|required|string|max:100',
                'document_type' => 'sometimes|required|string|max:20',
                'document_number' => 'sometimes|required|string|max:50|unique:clients,document_number,' . $id,
                'email' => 'nullable|email|max:100',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'department_id' => 'sometimes|required|exists:departments,id',
                'city_id' => 'sometimes|required|exists:cities,id',
                'address' => 'sometimes|required|string',
                'monthly_consumption_kwh' => 'sometimes|required|numeric|min:0',
                'tariff_cop_kwh' => 'sometimes|required|numeric|min:0',
                'responsible_user_id' => 'nullable|exists:users,id',
                'notes' => 'nullable|string',
                'is_active' => 'boolean',
                'primary_contact' => 'nullable|array',
                'primary_contact.name' => 'required_with:primary_contact|string|max:100',
                'primary_contact.email' => 'nullable|email|max:100',
                'primary_contact.phone' => 'nullable|string|max:20',
                'primary_contact.position' => 'nullable|string|max:100'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }

            $data = $request->all();
            // Si no se envía responsible_user_id, mantener el valor actual
            // Solo asignar Auth::id() si es una creación nueva
            if (!isset($data['responsible_user_id'])) {
                unset($data['responsible_user_id']); // No modificar el campo existente
            }

            $client = $this->clientService->updateClient($client, $data);

            return $this->updatedResponse($client, 'Cliente actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar el cliente');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $client = Client::findOrFail($id);
            $this->clientService->deleteClient($client);

            return $this->deletedResponse('Cliente eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar el cliente');
        }
    }

    /**
     * Get clients by user
     */
    public function getByUser(string $userId): JsonResponse
    {
        try {
            $onlyActive = request()->boolean('active_only', true);
            $clients = $this->clientService->getClientsByUser((int) $userId, $onlyActive);

            return $this->successResponse($clients, 'Clientes del usuario obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los clientes del usuario');
        }
    }

    /**
     * Get client statistics
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->clientService->getClientStatistics();

            return $this->successResponse($stats, 'Estadísticas de clientes obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener las estadísticas');
        }
    }

    /**
     * Bulk delete clients
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'client_ids' => 'required|array|min:1|max:50', // Máximo 50 para evitar sobrecarga
                'client_ids.*' => 'required|integer|exists:clients,id'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }

            $clientIds = $request->input('client_ids');
            $deletedCount = $this->clientService->bulkDeleteClients($clientIds);

            return $this->successResponse([
                'deleted_count' => $deletedCount,
                'client_ids' => $clientIds
            ], "{$deletedCount} cliente(s) eliminado(s) exitosamente");
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar los clientes');
        }
    }
}
