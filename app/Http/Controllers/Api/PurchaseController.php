<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PurchaseController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $purchases = Purchase::with(['supplier', 'costCenter', 'project', 'user'])->get();
            return response()->json($purchases);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener compras',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function summary(): JsonResponse
    {
        try {
            $purchases = Purchase::with([
                'supplier:id,name',
                'project:id,name'
            ])->get();

            $result = $purchases->map(function ($purchase) {
                return [
                    'id' => $purchase->id,
                    'numero' => $purchase->invoice_number,
                    'proveedor' => $purchase->supplier ? $purchase->supplier->name : '',
                    'proyecto' => $purchase->project ? $purchase->project->name : '',
                    'monto' => (float) $purchase->total_amount,
                    'estado' => $purchase->status,
                    'fecha' => $purchase->date ? $purchase->date->format('Y-m-d') : '',
                    'metodo_pago' => $purchase->payment_method
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener resumen de facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'invoice_number' => 'required|string|max:50|unique:purchases',
                'date' => 'required|date',
                'total_amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:Efectivo,Transferencia,Tarjeta,Cheque,Crédito',
                'status' => 'sometimes|in:Pendiente,Pagado,Cancelado',
                'description' => 'nullable|string',
                'supplier_id' => 'required|exists:suppliers,id',
                'cost_center_id' => 'required|exists:cost_centers,id',
                'project_id' => 'nullable|exists:projects,id',
                'user_id' => 'required|exists:users,id'
            ]);

            $purchase = Purchase::create($validatedData);
            
            return response()->json([
                'message' => 'Compra registrada',
                'purchase' => $purchase
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al registrar compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $purchase = Purchase::findOrFail($id);
            
            $validatedData = $request->validate([
                'invoice_number' => 'sometimes|string|max:50|unique:purchases,invoice_number,' . $id,
                'date' => 'sometimes|date',
                'total_amount' => 'sometimes|numeric|min:0',
                'payment_method' => 'sometimes|in:Efectivo,Transferencia,Tarjeta,Cheque,Crédito',
                'status' => 'sometimes|in:Pendiente,Pagado,Cancelado',
                'description' => 'nullable|string',
                'supplier_id' => 'sometimes|exists:suppliers,id',
                'cost_center_id' => 'sometimes|exists:cost_centers,id',
                'project_id' => 'nullable|exists:projects,id',
                'user_id' => 'sometimes|exists:users,id'
            ]);

            $purchase->update($validatedData);
            
            return response()->json([
                'message' => 'Compra actualizada',
                'purchase' => $purchase
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $purchase = Purchase::findOrFail($id);
            $purchase->delete();
            
            return response()->json([
                'message' => 'Compra eliminada'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar compra',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}