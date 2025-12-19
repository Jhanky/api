<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
            ]);

            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'mobile' => $request->mobile,
                'position' => $request->position,
                'is_active' => true,
            ]);

            // Assign default role
            $user->assignRole('user');

            $token = $user->createToken('auth_token')->plainTextToken;

            \Log::info('AuthController: User registered successfully', [
                'user_id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
            ]);

            return $this->createdResponse([
                'user' => $user->load('roles'),
                'token' => $token,
            ], 'Usuario registrado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al registrar usuario');
        }
    }

    
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required_without:email|string|max:255',
            'email' => 'required_without:username|email|max:255',
            'password' => 'required|string|min:1',
        ]);

        // Buscar usuario por email o username
        $user = User::where(function ($query) use ($request) {
            if ($request->filled('email')) {
                $query->where('email', $request->email);
            }
            if ($request->filled('username')) {
                $query->orWhere('username', $request->username);
            }
        })->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            \Log::warning('AuthController: Login failed - invalid credentials', [
                'identifier' => $request->email ?? $request->username,
                'identifier_type' => $request->filled('email') ? 'email' : 'username'
            ]);

            return $this->errorResponse(
                'Las credenciales proporcionadas son incorrectas',
                [],
                401
            );
        }

        if (!$user->is_active) {
            \Log::warning('AuthController: Login failed - account deactivated', [
                'user_id' => $user->id,
                'identifier' => $request->email ?? $request->username
            ]);

            return $this->forbiddenResponse('La cuenta está desactivada');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // Cargar roles del usuario explícitamente
        $userWithRoles = $user->load('roles');

        // Actualizar último login
        $user->update(['last_login_at' => now()]);

        \Log::info('AuthController: Login successful', [
            'user_id' => $user->id,
            'identifier' => $request->email ?? $request->username,
            'identifier_type' => $request->filled('email') ? 'email' : 'username',
            'roles_loaded' => $userWithRoles->relationLoaded('roles'),
            'roles_count' => $userWithRoles->roles->count(),
            'user_roles' => $userWithRoles->roles->pluck('name')->toArray()
        ]);

        return $this->successResponse([
            'user' => $userWithRoles,
            'token' => $token,
        ], 'Inicio de sesión exitoso');
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();

            \Log::info('AuthController: User logged out', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);

            return $this->successResponse(null, 'Sesión cerrada exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al cerrar sesión');
        }
    }

    /**
     * Get authenticated user.
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
     * Refresh token.
     */
    public function refresh(Request $request)
    {
        try {
            $user = $request->user();
            $user->currentAccessToken()->delete();
            $token = $user->createToken('auth_token')->plainTextToken;

            \Log::info('AuthController: Token refreshed', [
                'user_id' => $user->id,
                'username' => $user->username,
            ]);

            return $this->successResponse([
                'token' => $token,
            ], 'Token actualizado exitosamente');
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar token');
        }
    }
}
