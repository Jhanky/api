<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientType;
use App\Services\ClientTypeService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ClientTypeController extends Controller
{
    use ApiResponseTrait;

    protected ClientTypeService $clientTypeService;

    public function __construct(ClientTypeService $clientTypeService)
    {
        $this->clientTypeService = $clientTypeService;
    }

    /**
     * Display a listing of client types.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->only([
                'search', 'is_active', 'has_clients', 'sort_by', 'sort_order'
            ]);

            $perPage = $request->get('per_page', 15);
            $clientTypes = $this->clientTypeService->getClientTypes($filters, $perPage);

            return $this->paginationResponse(
                $clientTypes,
                'Tipos de cliente obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los tipos de cliente');
        }
    }

    /**
     * Get active client types (for dropdowns).
     */
    public function active(): JsonResponse
    {
        try {
            $clientTypes = $this->clientTypeService->getActiveClientTypes();

            return $this->successResponse(
                $clientTypes,
                'Tipos de cliente activos obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los tipos de cliente activos');
        }
    }

    /**
     * Store a newly created client type.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50|unique:client_types,name',
                'slug' => 'nullable|string|max:50|unique:client_types,slug',
                'description' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }

            $data = $request->all();
            $clientType = $this->clientTypeService->createClientType($data);

            return $this->createdResponse($clientType, 'Tipo de cliente creado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear el tipo de cliente');
        }
    }

    /**
     * Display the specified client type.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $clientType = $this->clientTypeService->getClientTypeById((int) $id);

            return $this->successResponse($clientType, 'Tipo de cliente obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Tipo de cliente no encontrado');
        }
    }

    /**
     * Update the specified client type.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $clientType = ClientType::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:50|unique:client_types,name,' . $id,
                'slug' => 'nullable|string|max:50|unique:client_types,slug,' . $id,
                'description' => 'nullable|string|max:255',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }

            $clientType = $this->clientTypeService->updateClientType($clientType, $request->all());

            return $this->updatedResponse($clientType, 'Tipo de cliente actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar el tipo de cliente');
        }
    }

    /**
     * Remove the specified client type.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $clientType = ClientType::findOrFail($id);
            $this->clientTypeService->deleteClientType($clientType);

            return $this->deletedResponse('Tipo de cliente eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar el tipo de cliente');
        }
    }

    /**
     * Get client type statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = $this->clientTypeService->getClientTypeStatistics();

            return $this->successResponse($stats, 'Estadísticas de tipos de cliente obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener las estadísticas');
        }
    }
}
