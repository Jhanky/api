<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientContactPerson;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ClientContactPersonController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of contact persons for a client.
     */
    public function index(string $clientId, Request $request): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);

            $query = $client->contactPersons();

            // Apply filters
            if ($request->has('is_primary')) {
                $query->where('is_primary', $request->boolean('is_primary'));
            }

            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('position', 'like', "%{$search}%");
                });
            }

            // Apply sorting - primary contacts first, then by specified criteria
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderByRaw('is_primary DESC, ' . $sortBy . ' ' . $sortOrder);

            $contacts = $query->paginate(15);

            return $this->paginationResponse(
                $contacts,
                'Personas de contacto obtenidas exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener las personas de contacto');
        }
    }

    /**
     * Store a new contact person for a client.
     */
    public function store(string $clientId, Request $request): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:100',
                'email' => 'nullable|email|max:100',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'is_primary' => 'boolean',
                'notes' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }

            DB::beginTransaction();

            // If setting as primary, unset other primary contacts
            if ($request->boolean('is_primary', false)) {
                $client->contactPersons()->where('is_primary', true)->update(['is_primary' => false]);
            }

            $contact = $client->contactPersons()->create($request->all());

            DB::commit();

            return $this->createdResponse($contact, 'Persona de contacto creada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Error al crear la persona de contacto');
        }
    }

    /**
     * Display the specified contact person.
     */
    public function show(string $clientId, string $contactId): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);
            $contact = $client->contactPersons()->findOrFail($contactId);

            return $this->successResponse($contact, 'Persona de contacto obtenida exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Persona de contacto no encontrada');
        }
    }

    /**
     * Update the specified contact person.
     */
    public function update(string $clientId, string $contactId, Request $request): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);
            $contact = $client->contactPersons()->findOrFail($contactId);

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:100',
                'email' => 'nullable|email|max:100',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'department' => 'nullable|string|max:100',
                'is_primary' => 'boolean',
                'notes' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors()->toArray());
            }

            DB::beginTransaction();

            // If setting as primary, unset other primary contacts
            if ($request->boolean('is_primary', false) && !$contact->is_primary) {
                $client->contactPersons()->where('is_primary', true)->update(['is_primary' => false]);
            }

            $contact->update($request->all());

            DB::commit();

            return $this->updatedResponse($contact, 'Persona de contacto actualizada exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Error al actualizar la persona de contacto');
        }
    }

    /**
     * Remove the specified contact person.
     */
    public function destroy(string $clientId, string $contactId): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);
            $contact = $client->contactPersons()->findOrFail($contactId);

            // Prevent deletion of primary contact if it's the only one
            if ($contact->is_primary && $client->contactPersons()->where('is_primary', false)->count() === 0) {
                return $this->errorResponse(
                    'No se puede eliminar el contacto principal si es el único contacto del cliente',
                    [],
                    400
                );
            }

            $contact->delete();

            return $this->deletedResponse('Persona de contacto eliminada exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar la persona de contacto');
        }
    }

    /**
     * Set a contact person as primary.
     */
    public function setPrimary(string $clientId, string $contactId): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);
            $contact = $client->contactPersons()->findOrFail($contactId);

            if ($contact->is_primary) {
                return $this->errorResponse('Esta persona ya es el contacto principal', [], 400);
            }

            DB::beginTransaction();

            // Unset all primary contacts for this client
            $client->contactPersons()->where('is_primary', true)->update(['is_primary' => false]);

            // Set this contact as primary
            $contact->update(['is_primary' => true]);

            DB::commit();

            return $this->updatedResponse($contact, 'Contacto principal establecido exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleException($e, 'Error al establecer el contacto principal');
        }
    }

    /**
     * Get primary contact for a client.
     */
    public function getPrimary(string $clientId): JsonResponse
    {
        try {
            $client = Client::findOrFail($clientId);
            $primaryContact = $client->primaryContact();

            // Return success response with null data if no primary contact exists
            return $this->successResponse($primaryContact, 'Contacto principal obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener el contacto principal');
        }
    }


}
