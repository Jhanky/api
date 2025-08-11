<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\JsonResponse;

class PurchaseStatsController extends Controller
{
    public function stats(): JsonResponse
    {
        $total_facturas = Purchase::count();
        $monto_total = Purchase::sum('total_amount');
        $facturas_pendientes = Purchase::where('status', 'Pendiente')->count();
        $facturas_pagadas = Purchase::where('status', 'Pagado')->count();
        $facturas_canceladas = Purchase::where('status', 'Cancelado')->count();
        $promedio_factura = $total_facturas > 0 ? round($monto_total / $total_facturas) : 0;

        return response()->json([
            'total_facturas' => $total_facturas,
            'monto_total' => $monto_total,
            'facturas_pendientes' => $facturas_pendientes,
            'facturas_pagadas' => $facturas_pagadas,
            'facturas_canceladas' => $facturas_canceladas,
            'promedio_factura' => $promedio_factura,
        ]);
    }
}
