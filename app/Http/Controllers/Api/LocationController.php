<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LocationController extends Controller
{
    /**
     * Obtener todos los departamentos únicos
     */
    public function getDepartments(): JsonResponse
    {
        try {
            $departments = Department::select('id', 'name')
                                   ->orderBy('name')
                                   ->get()
                                   ->map(function($department) {
                                       return [
                                           'id' => $department->id,
                                           'name' => $department->name
                                       ];
                                   });

            return response()->json([
                'success' => true,
                'data' => $departments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener departamentos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener ciudades por departamento
     */
    public function getCitiesByDepartment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'department' => 'required|integer|exists:departments,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'ID de departamento requerido y válido',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $cities = City::where('department_id', $request->department)
                         ->with('department')
                         ->select('id', 'name', 'department_id')
                         ->orderBy('name')
                         ->get()
                         ->map(function($city) {
                             return [
                                 'id' => $city->id,
                                 'location_id' => $city->id,
                                 'municipality' => $city->name,
                                 'department_name' => $city->department->name ?? null
                             ];
                         });

            return response()->json([
                'success' => true,
                'data' => $cities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ciudades',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Listar todas las ubicaciones con filtros
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Location::query();

            // Filtros
            if ($request->has('department')) {
                $query->byDepartment($request->department);
            }

            if ($request->has('municipality')) {
                $query->byMunicipality($request->municipality);
            }

            if ($request->has('min_radiation') || $request->has('max_radiation')) {
                $query->byRadiationRange(
                    $request->get('min_radiation'),
                    $request->get('max_radiation')
                );
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('department', 'like', "%{$search}%")
                      ->orWhere('municipality', 'like', "%{$search}%");
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'department');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $locations = $query->paginate($perPage);

            return response()->json($locations);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ubicaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear nueva ubicación
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'department' => 'required|string|max:50',
            'municipality' => 'required|string|max:50',
            'radiation' => 'required|numeric|min:0|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $location = Location::create($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Ubicación creada exitosamente',
                'data' => $location
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear ubicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar ubicación específica
     */
    public function show($id): JsonResponse
    {
        try {
            $location = Location::findOrFail($id);
            
            return response()->json([
                'success' => true,
                'data' => $location
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ubicación no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar ubicación
     */
    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'department' => 'sometimes|string|max:50',
            'municipality' => 'sometimes|string|max:50',
            'radiation' => 'sometimes|numeric|min:0|max:10'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $location = Location::findOrFail($id);
            $location->update($request->all());
            
            return response()->json([
                'success' => true,
                'message' => 'Ubicación actualizada exitosamente',
                'data' => $location
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar ubicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar ubicación
     */
    public function destroy($id): JsonResponse
    {
        try {
            $location = Location::findOrFail($id);
            $location->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Ubicación eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar ubicación',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de ubicaciones
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_locations' => Location::count(),
                'total_departments' => Location::distinct('department')->count(),
                'avg_radiation' => Location::avg('radiation'),
                'max_radiation' => Location::max('radiation'),
                'min_radiation' => Location::min('radiation'),
                'top_radiation_locations' => Location::orderBy('radiation', 'desc')
                                                   ->limit(5)
                                                   ->get(['municipality', 'department', 'radiation']),
                'departments_count' => Location::select('department')
                                              ->selectRaw('COUNT(*) as cities_count')
                                              ->groupBy('department')
                                              ->orderBy('cities_count', 'desc')
                                              ->get()
            ];
            
            return response()->json([
                'success' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
