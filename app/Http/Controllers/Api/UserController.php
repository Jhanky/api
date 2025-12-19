<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        try {
            $query = User::with('roles');

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // Filter by role
            if ($request->has('role')) {
                $query->whereHas('roles', function ($q) use ($request) {
                    $q->where('name', $request->role);
                });
            }

            $users = $query->paginate($request->get('per_page', 15));

            // Transform users to include role information
            $users->getCollection()->transform(function ($user) {
                $user->role_id = $user->roles->first()?->id;
                $user->role_name = $user->roles->first()?->name;
                return $user;
            });

            return $this->paginationResponse(
                $users,
                'Usuarios obtenidos exitosamente',
                ['stats' => $this->getUserStats()]
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener usuarios');
        }
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:255|unique:users,username',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'theme' => 'nullable|in:light,dark,system',
                'is_active' => 'boolean',
                'role_id' => 'required|exists:roles,id',
            ]);

            $user = User::create([
                'username' => $request->username,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'position' => $request->position,
                'theme' => $request->get('theme', 'system'),
                'is_active' => $request->get('is_active', true),
            ]);

            // Asignar el rol especificado por ID
            if ($request->role_id) {
                $user->roles()->attach($request->role_id, [
                    'assigned_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            \Log::info('UserController: User created successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $request->role,
                'created_by' => auth()->id(),
            ]);

            return $this->createdResponse(
                $user->load('roles'),
                'Usuario creado exitosamente con rol asignado'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al crear usuario');
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        try {
            $userData = $user->load('roles');
            // Agregar información de rol para compatibilidad
            $userData->role_id = $userData->roles->first()?->role_id;
            $userData->role_name = $userData->roles->first()?->name;

            return $this->successResponse(
                $userData,
                'Usuario obtenido exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener usuario');
        }
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        try {
            $request->validate([
                'username' => 'sometimes|required|string|max:255|unique:users,username,' . $user->id,
                'name' => 'sometimes|required|string|max:255',
                'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
                'password' => 'nullable|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
                'theme' => 'nullable|in:light,dark,system',
                'is_active' => 'boolean',
                'role_id' => 'nullable|exists:roles,id',
            ]);

            $data = $request->only([
                'username', 'name', 'email', 'phone', 'mobile',
                'position', 'theme', 'is_active'
            ]);

            if ($request->filled('password')) {
                $data['password'] = Hash::make($request->password);
            }

            $user->update($data);

            // Actualizar rol si se proporciona
            if ($request->filled('role_id')) {
                // Remover roles existentes
                $user->roles()->detach();
                
                // Asignar nuevo rol
                $user->roles()->attach($request->role_id, [
                    'assigned_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            \Log::info('UserController: User updated successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'updated_by' => auth()->id(),
                'changes' => array_keys($data),
            ]);

            return $this->updatedResponse(
                $user->load('roles'),
                'Usuario actualizado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar usuario');
        }
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        try {
            // Verificar que no se elimine a sí mismo
            if (request()->user()->id === $user->id) {
                return $this->errorResponse(
                    'No puedes eliminar tu propia cuenta',
                    [],
                    400
                );
            }

            $user->delete();

            \Log::info('UserController: User deleted successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'deleted_by' => auth()->id(),
            ]);

            return $this->deletedResponse('Usuario eliminado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al eliminar usuario');
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user)
    {
        try {
            $user->update(['is_active' => !$user->is_active]);
            
            \Log::info('UserController: User status toggled', [
                'user_id' => $user->id,
                'new_status' => $user->is_active,
                'toggled_by' => auth()->id(),
            ]);

            return $this->successResponse(
                $user->load('roles'),
                'Estado del usuario actualizado exitosamente'
            );
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cambiar estado del usuario');
        }
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        try {
            $user = $request->user()->load('roles');

            return $this->successResponse([
                'user' => $user,
            ], 'Información del usuario obtenida exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener información del usuario');
        }
    }

    /**
     * Get the authenticated user's ID.
     */
    public function getUserId(Request $request)
    {
        try {
            return $this->successResponse([
                'user_id' => $request->user()->id,
            ], 'ID de usuario obtenido exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener ID de usuario');
        }
    }

    /**
     * Upload user profile photo.
     */
    public function uploadProfilePhoto(Request $request, User $user)
    {
        try {
            $request->validate([
                'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Delete old profile photo
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete('profile-photos/' . basename($user->profile_photo_path));
            }

            $file = $request->file('profile_photo');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->storeAs('profile-photos', $filename, 'public');

            $user->update(['profile_photo_path' => $filename]);

            \Log::info('UserController: Profile photo uploaded', [
                'user_id' => $user->id,
                'filename' => $filename,
            ]);

            return $this->successResponse([
                'profile_photo_url' => $filename,
                'user' => $user->load('roles'),
            ], 'Foto de perfil subida exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al subir foto de perfil');
        }
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Request $request, User $user)
    {
        try {
            $request->validate([
                'role' => 'required|string|exists:roles,name',
            ]);

            $user->assignRole($request->role);

            \Log::info('UserController: Role assigned to user', [
                'user_id' => $user->id,
                'role' => $request->role,
                'assigned_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'user' => $user->load('roles'),
            ], 'Rol asignado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al asignar rol');
        }
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(User $user, $roleName)
    {
        try {
            $user->removeRole($roleName);

            \Log::info('UserController: Role removed from user', [
                'user_id' => $user->id,
                'role' => $roleName,
                'removed_by' => auth()->id(),
            ]);

            return $this->successResponse([
                'user' => $user->load('roles'),
            ], 'Rol removido exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al remover rol');
        }
    }

    /**
     * Get user options for dropdowns/selects.
     */
    public function getUserOptions()
    {
        try {
            $users = User::where('is_active', true)
                        ->select('id', 'name', 'email', 'username')
                        ->orderBy('name')
                        ->get();

            $userOptions = $users->map(function ($user) {
                return [
                    'value' => $user->id,
                    'label' => $user->name . ' (' . $user->email . ')',
                    'name' => $user->name,
                    'email' => $user->email,
                    'username' => $user->username,
                ];
            });

            // Get active roles for user management
            $roles = \App\Models\Role::where('is_active', true)
                        ->select('role_id', 'name', 'slug')
                        ->orderBy('name')
                        ->get();

            $roleOptions = $roles->map(function ($role) {
                return [
                    'role_id' => $role->role_id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                ];
            });

            return $this->successResponse([
                'options' => [
                    'users' => $userOptions,
                    'roles' => $roleOptions,
                ],
            ], 'Opciones de usuarios y roles obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener opciones de usuarios');
        }
    }

    /**
     * Get user statistics.
     */
    public function statistics()
    {
        try {
            $stats = $this->getUserStats();

            return $this->successResponse([
                'statistics' => $stats,
            ], 'Estadísticas de usuarios obtenidas exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener estadísticas de usuarios');
        }
    }

    /**
     * Get user statistics helper method.
     */
    private function getUserStats()
    {
        return [
            'total' => User::count(),
            'active' => User::where('is_active', true)->count(),
            'inactive' => User::where('is_active', false)->count(),
            'administrators' => User::whereHas('roles', function ($q) {
                $q->where('slug', 'admin');
            })->count(),
            'managers' => User::whereHas('roles', function ($q) {
                $q->whereIn('slug', ['project-manager', 'sales-manager']);
            })->count(),
            'technicians' => User::whereHas('roles', function ($q) {
                $q->where('slug', 'technician');
            })->count(),
            'others' => User::whereDoesntHave('roles', function ($q) {
                $q->whereIn('slug', ['admin', 'project-manager', 'sales-manager', 'technician']);
            })->count(),
        ];
    }
}
