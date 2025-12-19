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
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        // Verificar autenticación
        if (!Auth::check()) {
            \Log::warning('CheckRole: Usuario no autenticado', [
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No autenticado',
                'error' => 'Unauthenticated'
            ], 401);
        }

        $user = Auth::user();

        \Log::info('CheckRole: Verificando roles', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'required_roles_string' => $roles,
            'url' => $request->fullUrl()
        ]);

        // Cargar explícitamente la relación de roles si no está cargada
        if (!$user->relationLoaded('roles')) {
            $user->load('roles');
            \Log::info('CheckRole: Roles cargados desde BD', [
                'roles_loaded' => $user->relationLoaded('roles'),
                'roles_count' => $user->roles->count()
            ]);
        }

        // Convertir string de roles separados por coma a array
        $requiredRoles = array_map('trim', explode(',', $roles));

        \Log::info('CheckRole: Verificación de roles', [
            'required_roles' => $requiredRoles,
            'user_roles' => $user->roles->pluck('name')->toArray(),
            'user_role_ids' => $user->roles->pluck('role_id')->toArray()
        ]);

        // Verificar si el usuario tiene al menos uno de los roles requeridos
        $hasRequiredRole = false;
        $matchedRole = null;

        // Log detallado de roles del usuario
        $userRoles = $user->roles->pluck('name')->toArray();
        $userRoleSlugs = $user->roles->pluck('slug')->toArray();

        \Log::info('CheckRole: Verificación detallada de roles', [
            'user_roles_names' => $userRoles,
            'user_roles_slugs' => $userRoleSlugs,
            'required_roles' => $requiredRoles,
            'url' => $request->fullUrl()
        ]);

        foreach ($requiredRoles as $role) {
            // Verificar tanto por nombre como por slug
            $hasRoleByName = $user->hasRole($role);
            $hasRoleBySlug = in_array($role, $userRoleSlugs);

            \Log::info('CheckRole: Verificando rol específico', [
                'checking_role' => $role,
                'has_role_by_name' => $hasRoleByName,
                'has_role_by_slug' => $hasRoleBySlug,
                'final_result' => $hasRoleByName || $hasRoleBySlug
            ]);

            if ($hasRoleByName || $hasRoleBySlug) {
                $hasRequiredRole = true;
                $matchedRole = $role;
                break;
            }
        }

        // Verificación adicional: si el usuario tiene rol 'administrador', permitir acceso
        if (!$hasRequiredRole && in_array('administrador', $userRoles)) {
            \Log::info('CheckRole: Acceso permitido por rol administrador', [
                'user_id' => $user->id,
                'user_roles' => $userRoles
            ]);
            $hasRequiredRole = true;
            $matchedRole = 'administrador (override)';
        }

        if (!$hasRequiredRole) {
            \Log::warning('CheckRole: Acceso denegado - rol insuficiente', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'required_roles' => $requiredRoles,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'url' => $request->fullUrl()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No tienes el rol necesario para realizar esta acción',
                'error' => 'Insufficient role',
                'required_roles' => $requiredRoles,
                'user_roles' => $user->roles->pluck('name')->toArray(),
                'debug_info' => [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'roles_loaded' => $user->relationLoaded('roles'),
                    'roles_count' => $user->roles->count(),
                    'matched_role' => $matchedRole,
                    'url' => $request->fullUrl()
                ]
            ], 403);
        }

        \Log::info('CheckRole: Acceso permitido', [
            'user_id' => $user->id,
            'matched_role' => $matchedRole,
            'url' => $request->fullUrl()
        ]);

        // Agregar información del usuario a la request
        $request->attributes->set('user_roles', $user->roles->pluck('name')->toArray());
        $request->attributes->set('user_permissions', []); // Sistema sin permisos específicos

        return $next($request);
    }
}
