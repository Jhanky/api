<?php

namespace App\Http\Middleware\Apk;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MobileAuth
{
    /**
     * Middleware de autenticación optimizado para móvil
     * Incluye validaciones específicas para aplicaciones móviles
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada. Por favor, inicia sesión nuevamente.',
                'error' => 'Unauthenticated',
                'action_required' => 'login'
            ], 401);
        }

        $user = Auth::user();

        // Verificar si el usuario está activo
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
                'error' => 'Account deactivated',
                'action_required' => 'contact_admin'
            ], 403);
        }

        // Verificar si el token es válido para móvil
        $currentToken = $request->user()->currentAccessToken();
        if ($currentToken && !in_array('mobile', $currentToken->abilities)) {
            return response()->json([
                'success' => false,
                'message' => 'Token no válido para aplicación móvil',
                'error' => 'Invalid token scope',
                'action_required' => 'refresh_token'
            ], 403);
        }

        // Agregar información del usuario a la request
        $request->attributes->set('mobile_user', [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name')
        ]);

        return $next($request);
    }
}
