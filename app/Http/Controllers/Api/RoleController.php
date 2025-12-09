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
        $query = Role::with('users');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('display_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $roles = $query->paginate($request->get('per_page', 15));

        return response()->json($roles);
    }

    /**
     * Store a newly created role.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $role = Role::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
            'description' => $request->description,
            'is_active' => $request->get('is_active', true),
        ]);

        return response()->json([
            'message' => 'Rol creado exitosamente',
            'role' => $role,
        ], Response::HTTP_CREATED);
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        return response()->json([
            'role' => $role->load('users'),
        ]);
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'string|max:255|unique:roles,name,' . $role->id,
            'display_name' => 'string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $role->update($request->only([
            'name', 'display_name', 'description', 'is_active'
        ]));

        return response()->json([
            'message' => 'Rol actualizado exitosamente',
            'role' => $role,
        ]);
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role)
    {
        // Verificar si el rol tiene usuarios asignados
        if ($role->users()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar el rol porque tiene usuarios asignados',
            ], Response::HTTP_BAD_REQUEST);
        }

        $role->delete();

        return response()->json([
            'message' => 'Rol eliminado exitosamente',
        ]);
    }

    /**
     * Get all active roles for dropdown/select.
     */
    public function getActiveRoles()
    {
        $roles = Role::where('is_active', true)
                    ->select('id', 'name', 'display_name')
                    ->get();

        return response()->json($roles);
    }

    /**
     * Get role statistics.
     */
    public function statistics()
    {
        $stats = [
            'total_roles' => Role::count(),
            'active_roles' => Role::where('is_active', true)->count(),
            'roles_with_users' => Role::whereHas('users')->count(),
            'roles_by_name' => [
                'administrador' => Role::where('name', 'administrador')->withCount('users')->first(),
                'comercial' => Role::where('name', 'comercial')->withCount('users')->first(),
                'tecnico' => Role::where('name', 'tecnico')->withCount('users')->first(),
            ]
        ];

        return response()->json($stats);
    }
}
