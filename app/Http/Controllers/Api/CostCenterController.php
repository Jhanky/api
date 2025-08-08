<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CostCenter;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CostCenterController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $costCenters = CostCenter::all();
            return response()->json($costCenters);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener centros de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'code' => 'required|string|max:20|unique:cost_centers',
                'name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:activo,inactivo'
            ]);

            $costCenter = CostCenter::create($validatedData);
            
            return response()->json([
                'message' => 'Centro de costo creado',
                'cost_center' => $costCenter
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $costCenter = CostCenter::findOrFail($id);
            return response()->json($costCenter);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Centro de costo no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $costCenter = CostCenter::findOrFail($id);
            
            $validatedData = $request->validate([
                'code' => 'sometimes|string|max:20|unique:cost_centers,code,' . $id,
                'name' => 'sometimes|string|max:100',
                'description' => 'nullable|string',
                'status' => 'sometimes|in:activo,inactivo'
            ]);

            $costCenter->update($validatedData);
            
            return response()->json([
                'message' => 'Centro de costo actualizado',
                'cost_center' => $costCenter
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $costCenter = CostCenter::findOrFail($id);
            
            // Verificar si el centro de costo tiene facturas asociadas
            $purchasesCount = Purchase::where('cost_center_id', $id)->count();
            
            if ($purchasesCount > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el centro de costo',
                    'error' => "El centro de costo tiene {$purchasesCount} factura(s) asociada(s). Elimine las facturas primero o contacte al administrador."
                ], 400);
            }
            
            $costCenter->delete();
            
            return response()->json([
                'message' => 'Centro de costo eliminado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}