<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientContactPerson;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClientService
{
    /**
     * Get paginated clients with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getClients(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Client::with([
            'clientType',
            'department',
            'city',
            'responsibleUser:id,name,email',
            'contactPersons' => function ($q) {
                $q->where('is_primary', true)->select('id', 'client_id', 'name', 'email', 'phone');
            }
        ]);

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get client by ID with full relationships
     *
     * @param int $id
     * @return Client
     */
    public function getClientById(int $id): Client
    {
        return Client::with([
            'clientType',
            'department',
            'city',
            'responsibleUser:id,name,email',
            'contactPersons'
        ])->findOrFail($id);
    }

    /**
     * Create a new client
     *
     * @param array $data
     * @return Client
     */
    public function createClient(array $data): Client
    {
        DB::beginTransaction();
        try {
            $client = Client::create($data);

            // Create primary contact if provided
            if (isset($data['primary_contact'])) {
                $this->createPrimaryContact($client, $data['primary_contact']);
            }

            DB::commit();
            return $client->load(['clientType', 'department', 'city', 'responsibleUser', 'contactPersons']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating client', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing client
     *
     * @param Client $client
     * @param array $data
     * @return Client
     */
    public function updateClient(Client $client, array $data): Client
    {
        DB::beginTransaction();
        try {
            $client->update($data);

            // Update primary contact if provided
            if (isset($data['primary_contact'])) {
                $this->updatePrimaryContact($client, $data['primary_contact']);
            }

            DB::commit();
            return $client->fresh(['clientType', 'department', 'city', 'responsibleUser', 'contactPersons']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating client', [
                'client_id' => $client->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a client
     *
     * @param Client $client
     * @return bool
     */
    public function deleteClient(Client $client): bool
    {
        DB::beginTransaction();
        try {
            // Soft delete related records first
            $client->contactPersons()->delete();

            $client->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting client', [
                'client_id' => $client->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get clients by user
     *
     * @param int $userId
     * @param bool $onlyActive
     * @return Collection
     */
    public function getClientsByUser(int $userId, bool $onlyActive = true): Collection
    {
        $query = Client::where('responsible_user_id', $userId)
                      ->with(['clientType:id,name', 'department:id,name', 'city:id,name']);

        if ($onlyActive) {
            $query->active();
        }

        return $query->get();
    }

    /**
     * Get client statistics
     *
     * @return array
     */
    public function getClientStatistics(): array
    {
        return [
            'total_clients' => Client::count(),
            'active_clients' => Client::active()->count(),
            'inactive_clients' => Client::where('is_active', false)->count(),
            'clients_by_type' => Client::selectRaw('client_type_id, COUNT(*) as count')
                                      ->with('clientType:id,name')
                                      ->groupBy('client_type_id')
                                      ->get(),
            'clients_by_department' => Client::selectRaw('department_id, COUNT(*) as count')
                                            ->with('department:id,name')
                                            ->groupBy('department_id')
                                            ->orderBy('count', 'desc')
                                            ->limit(10)
                                            ->get(),
            'total_consumption' => Client::sum('monthly_consumption_kwh'),
            'average_consumption' => Client::avg('monthly_consumption_kwh'),
            'average_energy_rate' => Client::avg('tariff_cop_kwh')
        ];
    }

    /**
     * Apply filters to client query
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filters
     * @return void
     */
    private function applyFilters($query, array $filters): void
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nic', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['client_type_id'])) {
            $query->where('client_type_id', $filters['client_type_id']);
        }

        if (!empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (!empty($filters['city_id'])) {
            $query->where('city_id', $filters['city_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['responsible_user_id'])) {
            $query->where('responsible_user_id', $filters['responsible_user_id']);
        }

        if (!empty($filters['min_consumption'])) {
            $query->where('monthly_consumption_kwh', '>=', $filters['min_consumption']);
        }

        if (!empty($filters['max_consumption'])) {
            $query->where('monthly_consumption_kwh', '<=', $filters['max_consumption']);
        }
    }

    /**
     * Create primary contact for client
     *
     * @param Client $client
     * @param array $contactData
     * @return ClientContactPerson
     */
    private function createPrimaryContact(Client $client, array $contactData): ClientContactPerson
    {
        // Set any existing primary contact to non-primary
        $client->contactPersons()->where('is_primary', true)->update(['is_primary' => false]);

        return $client->contactPersons()->create(array_merge($contactData, ['is_primary' => true]));
    }

    /**
     * Update primary contact for client
     *
     * @param Client $client
     * @param array $contactData
     * @return ClientContactPerson
     */
    private function updatePrimaryContact(Client $client, array $contactData): ClientContactPerson
    {
        $primaryContact = $client->contactPersons()->where('is_primary', true)->first();

        if ($primaryContact) {
            $primaryContact->update($contactData);
            return $primaryContact;
        }

        return $this->createPrimaryContact($client, $contactData);
    }

    /**
     * Bulk delete clients
     *
     * @param array $clientIds
     * @return int Number of deleted clients
     */
    public function bulkDeleteClients(array $clientIds): int
    {
        DB::beginTransaction();
        try {
            // Get clients to delete for logging
            $clientsToDelete = Client::whereIn('id', $clientIds)->get();

            // Delete related contact persons first
            ClientContactPerson::whereIn('client_id', $clientIds)->delete();

            // Delete clients
            $deletedCount = Client::whereIn('id', $clientIds)->delete();

            DB::commit();

            // Log the bulk deletion
            Log::info('Bulk client deletion completed', [
                'deleted_count' => $deletedCount,
                'client_ids' => $clientIds,
                'clients' => $clientsToDelete->pluck('name', 'id')->toArray()
            ]);

            return $deletedCount;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk client deletion', [
                'client_ids' => $clientIds,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
