<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(Request $request)
    {
        try {
            \Log::info('🔍 RoleController@index - Iniciando consulta de roles', [
                'params' => $request->all(),
                'user_id' => auth()->id()
            ]);

            $query = Role::with('users');

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('slug', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
                \Log::info('🔍 Aplicando filtro de búsqueda', ['search' => $search]);
            }

            // Filter by status
            if ($request->has('is_active')) {
                $isActive = $request->boolean('is_active');
                $query->where('is_active', $isActive);
                \Log::info('🔍 Aplicando filtro de estado', ['is_active' => $isActive]);
            }

            $perPage = $request->get('per_page', 15);
            $roles = $query->paginate($perPage);

            \Log::info('📊 Resultados de consulta', [
                'total_roles' => $roles->total(),
                'current_page' => $roles->currentPage(),
                'per_page' => $roles->perPage(),
                'items_count' => $roles->count()
            ]);

            // Agregar users_count a cada rol
            $roles->getCollection()->transform(function ($role) {
                $role->users_count = $role->users()->count();
                return $role;
            });

            return $this->paginationResponse(
                $roles,
                'Roles obtenidos exitosamente'
            );
        } catch (\Exception $e) {
            \Log::error('❌ Error en RoleController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'params' => $request->all()
            ]);

            return $this->handleException($e, 'Error al obtener roles');
        }
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles',
                'slug' => 'required|string|max:255|unique:roles',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $role = Role::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'is_active' => $request->get('is_active', true),
            ]);

            \Log::info('RoleController: Role created successfully', [
                'role_id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'user_id' => auth()->id(),
            ]);

            return $this->createdResponse(
                $role->load('users'),
                'Rol creado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear rol');
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        try {
            $roleData = $role->load('users');
            $roleData->users_count = $roleData->users->count();

            return $this->successResponse([
                'role' => $roleData,
            ], 'Rol obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener rol');
        }
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        try {
            $request->validate([
                'name' => 'string|max:255|unique:roles,name,' . $role->id,
                'slug' => 'string|max:255|unique:roles,slug,' . $role->id,
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $role->update($request->only([
                'name', 'slug', 'description', 'is_active'
            ]));

            \Log::info('RoleController: Role updated successfully', [
                'role_id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'user_id' => auth()->id(),
            ]);

            return $this->updatedResponse(
                $role->load('users'),
                'Rol actualizado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar rol');
        }
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        try {
            // Verificar si el rol tiene usuarios asignados
            $usersCount = $role->users()->count();
            if ($usersCount > 0) {
                return $this->errorResponse(
                    "No se puede eliminar el rol porque tiene {$usersCount} usuario(s) asignado(s)",
                    [],
                    400
                );
            }

            // Verificar si es un rol del sistema
            if ($role->is_system_role) {
                return $this->errorResponse(
                    'No se puede eliminar un rol del sistema',
                    [],
                    400
                );
            }

            $role->delete();

            \Log::info('RoleController: Role deleted successfully', [
                'role_id' => $role->id,
                'name' => $role->name,
                'slug' => $role->slug,
                'user_id' => auth()->id(),
            ]);

            return $this->deletedResponse('Rol eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar rol');
        }
    }

    /**
     * Get all active roles for dropdown/select.
     */
    public function getActiveRoles()
    {
        try {
            $roles = Role::where('is_active', true)
                        ->select('id', 'name', 'slug')
                        ->orderBy('name')
                        ->get();

            return $this->successResponse([
                'roles' => $roles,
            ], 'Roles activos obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener roles activos');
        }
    }

    /**
     * Toggle role status (active/inactive).
     */
    public function toggleStatus(Role $role)
    {
        try {
            $newStatus = !$role->is_active;
            $role->update(['is_active' => $newStatus]);

            \Log::info('RoleController: Role status toggled', [
                'role_id' => $role->id,
                'name' => $role->name,
                'old_status' => !$newStatus,
                'new_status' => $newStatus,
                'user_id' => auth()->id(),
            ]);

            return $this->updatedResponse([
                'role' => $role->load('users'),
                'status' => $newStatus ? 'activado' : 'desactivado',
            ], 'Estado del rol actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar el estado del rol');
        }
    }

    /**
     * Get role statistics.
     */
    public function statistics()
    {
        try {
            $stats = [
                'total' => Role::count(),
                'active' => Role::where('is_active', true)->count(),
                'inactive' => Role::where('is_active', false)->count(),
                'system_roles' => Role::where('is_system_role', true)->count(),
                'custom_roles' => Role::where('is_system_role', false)->count(),
                'with_users' => Role::has('users')->count(),
                'without_users' => Role::doesntHave('users')->count(),
            ];

            return $this->successResponse([
                'statistics' => $stats,
            ], 'Estadísticas de roles obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener estadísticas de roles');
        }
    }

    /**
     * Get available permissions (returns empty since system uses roles only).
     */
    public function getPermissions()
    {
        try {
            // Sistema basado en roles, no permisos específicos
            return $this->successResponse([
                'permissions' => [],
                'modules' => [],
                'flat_permissions' => []
            ], 'Permisos obtenidos exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener permisos');
        }
    }
}
