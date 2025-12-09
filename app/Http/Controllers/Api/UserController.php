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
        $query = User::with('roles');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
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

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        // Verificar que el usuario autenticado sea administrador
        if (!$request->user()->hasRole('administrador')) {
            return response()->json([
                'message' => 'No tienes permisos para crear usuarios. Solo los administradores pueden crear usuarios.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'role' => 'required|string|exists:roles,name', // Rol requerido
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'job_title' => $request->job_title,
        ]);

        // Asignar el rol especificado
        $user->assignRole($request->role);

        return response()->json([
            'message' => 'Usuario creado exitosamente con rol asignado',
            'user' => $user->load('roles'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return response()->json([
            'user' => $user->load('roles.permissions'),
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        // Verificar que el usuario autenticado sea administrador
        if (!$request->user()->hasRole('administrador')) {
            return response()->json([
                'message' => 'No tienes permisos para actualizar usuarios. Solo los administradores pueden actualizar usuarios.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'name' => 'string|max:255',
            'username' => 'string|max:255|unique:users,username,' . $user->id,
            'email' => 'string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8',
            'phone' => 'nullable|string|max:20',
            'job_title' => 'nullable|string|max:255',
            'role' => 'string|exists:roles,name', // Rol opcional en actualización
        ]);

        $data = $request->only([
            'name', 'username', 'email', 'phone', 'job_title'
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        // Actualizar rol si se proporciona
        if ($request->filled('role')) {
            $user->roles()->detach(); // Remover roles existentes
            $user->assignRole($request->role); // Asignar nuevo rol
        }

        return response()->json([
            'message' => 'Usuario actualizado exitosamente',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Verificar que el usuario autenticado sea administrador
        if (!request()->user()->hasRole('administrador')) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar usuarios. Solo los administradores pueden eliminar usuarios.',
            ], Response::HTTP_FORBIDDEN);
        }

        // Verificar que no se elimine a sí mismo
        if (request()->user()->id === $user->id) {
            return response()->json([
                'message' => 'No puedes eliminar tu propia cuenta.',
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->delete();

        return response()->json([
            'message' => 'Usuario eliminado exitosamente',
        ]);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('roles.permissions'),
        ]);
    }

    /**
     * Get the authenticated user's ID.
     */
    public function getUserId(Request $request)
    {
        return response()->json([
            'success' => true,
            'user_id' => $request->user()->id,
        ]);
    }

    /**
     * Upload user profile photo.
     */
    public function uploadProfilePhoto(Request $request, User $user)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Delete old profile photo
        if ($user->profile_photo) {
            Storage::disk('public')->delete('profile-photos/' . basename($user->profile_photo));
        }

        $file = $request->file('profile_photo');
        $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
        $file->storeAs('profile-photos', $filename, 'public');

        $user->update(['profile_photo' => $filename]);

        return response()->json([
            'message' => 'Foto de perfil subida exitosamente',
            'profile_photo_url' => $user->profile_photo,
        ]);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|string|exists:roles,name',
        ]);

        $user->assignRole($request->role);

        return response()->json([
            'message' => 'Rol asignado exitosamente',
            'user' => $user->load('roles'),
        ]);
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(User $user, $roleName)
    {
        $user->removeRole($roleName);

        return response()->json([
            'message' => 'Rol removido exitosamente',
            'user' => $user->load('roles'),
        ]);
    }
}
