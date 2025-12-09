<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * Verifica permisos específicos para el frontend web
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Verificar autenticación
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
                'error' => 'Unauthenticated'
            ], 401);
        }

        $user = Auth::user();

        // Verificar si el usuario tiene el permiso específico
        if (!$user->hasPermissionTo($permission)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
                'error' => 'Insufficient permissions',
                'required_permission' => $permission
            ], 403);
        }

        // Agregar información del usuario a la request para uso posterior
        $request->attributes->set('user_permissions', $user->getAllPermissions()->pluck('name'));

        return $next($request);
    }
}
