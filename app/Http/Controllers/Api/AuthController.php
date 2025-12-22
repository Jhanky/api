<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Register a new user.
     */
    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'username' => 'required|string|max:255|unique:users,username',
                'email' => 'required|string|email|max:255|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
                'phone' => 'nullable|string|max:20',
                'mobile' => 'nullable|string|max:20',
                'position' => 'nullable|string|max:100',
            ]);

            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['username'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'phone' => $validated['phone'] ?? null,
                'mobile' => $validated['mobile'] ?? null,
                'position' => $validated['position'] ?? null,
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

            return $this->successResponse([
                'user' => $user->load('roles'),
                'token' => $token,
            ], 'Usuario registrado exitosamente', 201, []);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al registrar usuario');
        }
    }

    /**
     * Login user.
     */
    public function login(LoginRequest $request)
    {
        try {
            $identifier = $request->getIdentifier();
            $identifierType = $request->getIdentifierType();

            // Buscar usuario por email o username
            $user = User::where(function ($query) use ($identifier, $identifierType) {
                if ($identifierType === 'email') {
                    $query->where('email', $identifier);
                } else {
                    $query->where('username', $identifier);
                }
            })->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                \Log::warning('AuthController: Login failed - invalid credentials', [
                    'identifier' => $identifier,
                    'identifier_type' => $identifierType
                ]);

                return $this->unauthorizedResponse('Las credenciales proporcionadas son incorrectas');
            }

            if (!$user->is_active) {
                \Log::warning('AuthController: Login failed - account deactivated', [
                    'user_id' => $user->id,
                    'identifier' => $identifier
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
                'identifier' => $identifier,
                'identifier_type' => $identifierType,
                'roles_loaded' => $userWithRoles->relationLoaded('roles'),
                'roles_count' => $userWithRoles->roles->count(),
                'user_roles' => $userWithRoles->roles->pluck('name')->toArray()
            ]);

            return $this->successResponse([
                'user' => $userWithRoles,
                'token' => $token,
            ], 'Inicio de sesión exitoso', 200, []);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error en el proceso de autenticación');
        }
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

            return $this->successResponse(null, 'Sesión cerrada exitosamente', 200, []);
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
            ], 'Información del usuario obtenida exitosamente', 200, []);
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
            ], 'Token actualizado exitosamente', 200, []);
        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al actualizar token');
        }
    }
}
