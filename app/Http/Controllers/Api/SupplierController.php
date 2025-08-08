<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $suppliers = Supplier::all();
            return response()->json($suppliers);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener proveedores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|max:100',
                'tax_id' => 'required|string|max:20|unique:suppliers',
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'contact_name' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:20'
            ]);

            $supplier = Supplier::create($validatedData);
            
            return response()->json([
                'message' => 'Proveedor creado',
                'supplier' => $supplier
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);
            return response()->json($supplier);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Proveedor no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            $validatedData = $request->validate([
                'name' => 'sometimes|string|max:100',
                'tax_id' => 'sometimes|string|max:20|unique:suppliers,tax_id,' . $id,
                'address' => 'nullable|string|max:255',
                'phone' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:100',
                'contact_name' => 'nullable|string|max:100',
                'contact_phone' => 'nullable|string|max:20'
            ]);

            $supplier->update($validatedData);
            
            return response()->json([
                'message' => 'Proveedor actualizado',
                'supplier' => $supplier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $supplier = Supplier::findOrFail($id);
            
            // Verificar si el proveedor tiene facturas asociadas
            $purchasesCount = Purchase::where('supplier_id', $id)->count();
            
            if ($purchasesCount > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el proveedor',
                    'error' => "El proveedor tiene {$purchasesCount} factura(s) asociada(s). Elimine las facturas primero o contacte al administrador."
                ], 400);
            }
            
            $supplier->delete();
            
            return response()->json([
                'message' => 'Proveedor eliminado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar proveedor',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}