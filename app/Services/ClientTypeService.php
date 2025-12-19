<?php

namespace App\Services;

use App\Models\ClientType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class ClientTypeService
{
    /**
     * Get paginated client types with filters
     *
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getClientTypes(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = ClientType::query();

        // Apply filters
        $this->applyFilters($query, $filters);

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'name';
        $sortOrder = $filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Get all active client types (for dropdowns)
     *
     * @return Collection
     */
    public function getActiveClientTypes(): Collection
    {
        return ClientType::active()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    /**
     * Get client type by ID
     *
     * @param int $id
     * @return ClientType
     */
    public function getClientTypeById(int $id): ClientType
    {
        return ClientType::findOrFail($id);
    }

    /**
     * Create a new client type
     *
     * @param array $data
     * @return ClientType
     */
    public function createClientType(array $data): ClientType
    {
        try {
            // Generate slug if not provided
            if (!isset($data['slug']) || empty($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name']);
            }

            return ClientType::create($data);
        } catch (\Exception $e) {
            Log::error('Error creating client type', [
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update an existing client type
     *
     * @param ClientType $clientType
     * @param array $data
     * @return ClientType
     */
    public function updateClientType(ClientType $clientType, array $data): ClientType
    {
        try {
            // Generate slug if name changed and slug not provided
            if (isset($data['name']) && (!isset($data['slug']) || empty($data['slug']))) {
                $data['slug'] = $this->generateSlug($data['name'], $clientType->id);
            }

            $clientType->update($data);
            return $clientType->fresh();
        } catch (\Exception $e) {
            Log::error('Error updating client type', [
                'client_type_id' => $clientType->id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a client type
     *
     * @param ClientType $clientType
     * @return bool
     */
    public function deleteClientType(ClientType $clientType): bool
    {
        try {
            // Check if client type has associated clients
            if ($clientType->clients()->count() > 0) {
                throw new \Exception('No se puede eliminar el tipo de cliente porque tiene clientes asociados');
            }

            $clientType->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Error deleting client type', [
                'client_type_id' => $clientType->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get client type statistics
     *
     * @return array
     */
    public function getClientTypeStatistics(): array
    {
        return [
            'total_client_types' => ClientType::count(),
            'active_client_types' => ClientType::active()->count(),
            'inactive_client_types' => ClientType::where('is_active', false)->count(),
            'client_types_with_clients' => ClientType::has('clients')->count(),
            'most_used_client_type' => ClientType::withCount('clients')
                ->orderBy('clients_count', 'desc')
                ->first(),
        ];
    }

    /**
     * Generate a unique slug for client type
     *
     * @param string $name
     * @param int|null $excludeId
     * @return string
     */
    private function generateSlug(string $name, ?int $excludeId = null): string
    {
        $slug = \Illuminate\Support\Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (ClientType::where('slug', $slug)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Apply filters to client type query
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
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        if (!empty($filters['has_clients'])) {
            if ($filters['has_clients']) {
                $query->has('clients');
            } else {
                $query->doesntHave('clients');
            }
        }
    }
}
