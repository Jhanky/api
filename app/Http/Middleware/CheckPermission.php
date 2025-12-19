<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Verificar autenticación
        if (!$request->user()) {
            Log::warning('CheckRole: Usuario no autenticado', [
                'role' => $role,
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Usuario no autenticado',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user = $request->user();

        // Verificar si el usuario tiene el rol requerido
        if (!$this->hasRole($user, $role)) {
            Log::warning('CheckRole: Rol denegado', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'role' => $role,
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos para realizar esta acción',
            ], Response::HTTP_FORBIDDEN);
        }

        Log::info('CheckRole: Rol concedido', [
            'user_id' => $user->id,
            'role' => $role,
            'route' => $request->route()?->getName(),
        ]);

        return $next($request);
    }

    /**
     * Verificar si el usuario tiene un rol específico.
     */
    private function hasRole($user, string $role): bool
    {
        // Cargar roles del usuario si no están cargados
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
        }

        // Verificar si el usuario tiene el rol requerido
        foreach ($user->roles as $userRole) {
            if ($userRole->name === $role || $userRole->slug === $role) {
                return true;
            }
        }

        return false;
    }
}
