<?php

namespace App\Mcp\Tools;

use App\Models\Client;
use PhpMcp\Server\Attributes\McpTool;

class ClientTool
{
    /**
     * Lista todos los clientes con opciones de filtrado.
     */
    #[McpTool(
        name: 'list_clients',
        description: 'Lista todos los clientes de VatioCore. Permite filtrar por nombre, NIC, email o teléfono. Retorna id, name, nic, email, phone, address, is_active y el tipo de cliente.'
    )]
    public function listClients(
        ?string $search = null,
        ?int $clientTypeId = null,
        ?bool $isActive = null,
        int $limit = 50
    ): array {
        $query = Client::with(['clientType', 'city.department']);

        if ($search) {
            $query->search($search);
        }

        if ($clientTypeId !== null) {
            $query->where('client_type_id', $clientTypeId);
        }

        if ($isActive !== null) {
            $query->where('is_active', $isActive);
        }

        $clients = $query->limit($limit)->get([
            'id', 'name', 'nic', 'email', 'phone', 'mobile', 'address',
            'client_type_id', 'city_id', 'is_active'
        ]);

        return [
            'success' => true,
            'count' => $clients->count(),
            'clients' => $clients->toArray()
        ];
    }

    /**
     * Obtiene el detalle completo de un cliente por su ID.
     */
    #[McpTool(
        name: 'get_client',
        description: 'Obtiene información detallada de un cliente específico por su ID. Incluye tipo de cliente, ciudad, departamento, personas de contacto y consumos.'
    )]
    public function getClient(int $clientId): array
    {
        $client = Client::with([
            'clientType',
            'city.department',
            'contactPersons',
            'responsibleUser'
        ])->find($clientId);

        if (!$client) {
            return [
                'success' => false,
                'error' => "Cliente no encontrado con ID: {$clientId}"
            ];
        }

        return [
            'success' => true,
            'client' => $client->toArray()
        ];
    }

    /**
     * Crea un nuevo cliente en el sistema.
     */
    #[McpTool(
        name: 'create_client',
        description: 'Crea un nuevo cliente en VatioCore. Campos requeridos: name. Opcionales: client_type_id, document_type, document_number, nic, email, phone, mobile, address, department_id, city_id, monthly_consumption_kwh, tariff_cop_kwh, notes.'
    )]
    public function createClient(
        string $name,
        ?int $clientTypeId = null,
        ?string $documentType = null,
        ?string $documentNumber = null,
        ?string $nic = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $mobile = null,
        ?string $address = null,
        ?int $departmentId = null,
        ?int $cityId = null,
        ?float $monthlyConsumptionKwh = null,
        ?float $tariffCopKwh = null,
        ?string $notes = null
    ): array {
        try {
            $data = array_filter([
                'name' => $name,
                'client_type_id' => $clientTypeId,
                'document_type' => $documentType,
                'document_number' => $documentNumber,
                'nic' => $nic,
                'email' => $email,
                'phone' => $phone,
                'mobile' => $mobile,
                'address' => $address,
                'department_id' => $departmentId,
                'city_id' => $cityId,
                'monthly_consumption_kwh' => $monthlyConsumptionKwh,
                'tariff_cop_kwh' => $tariffCopKwh,
                'notes' => $notes,
                'is_active' => true,
            ], fn($value) => $value !== null);

            $client = Client::create($data);

            return [
                'success' => true,
                'message' => 'Cliente creado exitosamente',
                'client' => $client->load(['clientType', 'city.department'])->toArray()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al crear cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Actualiza un cliente existente.
     */
    #[McpTool(
        name: 'update_client',
        description: 'Actualiza un cliente existente en VatioCore. Requiere el ID del cliente. Solo se actualizan los campos proporcionados.'
    )]
    public function updateClient(
        int $clientId,
        ?string $name = null,
        ?int $clientTypeId = null,
        ?string $documentType = null,
        ?string $documentNumber = null,
        ?string $nic = null,
        ?string $email = null,
        ?string $phone = null,
        ?string $mobile = null,
        ?string $address = null,
        ?int $departmentId = null,
        ?int $cityId = null,
        ?float $monthlyConsumptionKwh = null,
        ?float $tariffCopKwh = null,
        ?string $notes = null,
        ?bool $isActive = null
    ): array {
        try {
            $client = Client::find($clientId);

            if (!$client) {
                return [
                    'success' => false,
                    'error' => "Cliente no encontrado con ID: {$clientId}"
                ];
            }

            $data = array_filter([
                'name' => $name,
                'client_type_id' => $clientTypeId,
                'document_type' => $documentType,
                'document_number' => $documentNumber,
                'nic' => $nic,
                'email' => $email,
                'phone' => $phone,
                'mobile' => $mobile,
                'address' => $address,
                'department_id' => $departmentId,
                'city_id' => $cityId,
                'monthly_consumption_kwh' => $monthlyConsumptionKwh,
                'tariff_cop_kwh' => $tariffCopKwh,
                'notes' => $notes,
                'is_active' => $isActive,
            ], fn($value) => $value !== null);

            if (empty($data)) {
                return [
                    'success' => false,
                    'error' => 'No se proporcionaron campos para actualizar'
                ];
            }

            $client->update($data);

            return [
                'success' => true,
                'message' => 'Cliente actualizado exitosamente',
                'client' => $client->fresh()->load(['clientType', 'city.department'])->toArray()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => 'Error al actualizar cliente: ' . $e->getMessage()
            ];
        }
    }
}
