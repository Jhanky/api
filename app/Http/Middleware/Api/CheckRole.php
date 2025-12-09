<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Verifica roles específicos para el frontend web
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
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

        // Verificar si el usuario tiene el rol específico
        if (!$user->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes el rol necesario para realizar esta acción',
                'error' => 'Insufficient role',
                'required_role' => $role,
                'user_roles' => $user->getRoleNames()
            ], 403);
        }

        // Agregar información del usuario a la request
        $request->attributes->set('user_roles', $user->getRoleNames());
        $request->attributes->set('user_permissions', $user->getAllPermissions()->pluck('name'));

        return $next($request);
    }
}
