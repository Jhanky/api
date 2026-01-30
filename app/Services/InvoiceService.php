<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Crear una nueva factura
     */
    public function createInvoice(array $data, Request $request): Invoice
    {
        // Manejar subida de archivos
        $this->handleUploads($data, $request);

        // Asegurar fecha de emisión
        if (isset($request->invoice_date)) {
            $data['issue_date'] = $request->invoice_date;
        }
        
        // Mapear provider_id a supplier_id si es necesario
        if ($request->has('provider_id')) {
            $data['supplier_id'] = $request->provider_id;
        }

        $invoice = Invoice::create($data);
        $invoice->load(['supplier', 'costCenter', 'paymentMethod']);

        return $invoice;
    }

    /**
     * Actualizar una factura existente
     */
    public function updateInvoice(Invoice $invoice, array $data, Request $request): Invoice
    {
        // Manejar subida de archivos (reemplazo)
        $this->handleUploads($data, $request, $invoice);

        // Asegurar fecha de emisión
        if (isset($request->invoice_date)) {
            $data['issue_date'] = $request->invoice_date;
        }

        // Mapear provider_id a supplier_id
        if ($request->has('provider_id')) {
            $data['supplier_id'] = $request->provider_id;
        }

        $invoice->update($data);
        $invoice->load(['supplier', 'costCenter', 'paymentMethod']);

        return $invoice;
    }

    /**
     * Actualizar estado de la factura
     */
    public function updateStatus(Invoice $invoice, string $status): Invoice
    {
        $invoice->update(['status' => $status]);
        $invoice->load(['supplier', 'costCenter', 'paymentMethod']);
        return $invoice;
    }

    /**
     * Manejar lógica de subida de archivos
     */
    private function handleUploads(array &$data, Request $request, ?Invoice $invoice = null): void
    {
        // Soporte de pago
        if ($request->hasFile('payment_support')) {
            // Eliminar archivo anterior si existe y estamos actualizando
            if ($invoice && $invoice->payment_support && Storage::disk('public')->exists($invoice->payment_support)) {
                Storage::disk('public')->delete($invoice->payment_support);
            }
            
            $paymentSupportFile = $request->file('payment_support');
            $paymentSupportPath = $paymentSupportFile->store('invoices/payment_support', 'public');
            $data['payment_support'] = $paymentSupportPath;
        }

        // Archivo de factura
        if ($request->hasFile('invoice_file')) {
            // Eliminar archivo anterior si existe y estamos actualizando
            if ($invoice && $invoice->invoice_file && Storage::disk('public')->exists($invoice->invoice_file)) {
                Storage::disk('public')->delete($invoice->invoice_file);
            }
            
            $invoiceFile = $request->file('invoice_file');
            $invoiceFilePath = $invoiceFile->store('invoices/invoice_files', 'public');
            $data['invoice_file'] = $invoiceFilePath;
            
            // Opcional: Guardar metadatos del archivo si el modelo lo permite
            $data['invoice_file_name'] = $invoiceFile->getClientOriginalName();
            $data['invoice_file_type'] = $invoiceFile->getClientMimeType();
            $data['invoice_file_size'] = $invoiceFile->getSize();
        }
    }

    /**
     * Obtener reporte de cartera por edades
     */
    public function getAgingReport(): array
    {
        $now = now();
        
        // Obtener facturas pendientes (excluyendo cotizaciones)
        $invoices = Invoice::where('status', 'pendiente')
            ->whereNotNull('due_date')
            ->get();

        $report = [
            'current' => ['count' => 0, 'total' => 0, 'label' => 'Al día'],
            '1-30' => ['count' => 0, 'total' => 0, 'label' => '1-30 días'],
            '31-60' => ['count' => 0, 'total' => 0, 'label' => '31-60 días'],
            '61-90' => ['count' => 0, 'total' => 0, 'label' => '61-90 días'],
            'over_90' => ['count' => 0, 'total' => 0, 'label' => '> 90 días'],
            'total_pending' => 0
        ];

        foreach ($invoices as $invoice) {
            $remainingAmount = $invoice->remaining_amount; // Usar el accessor del modelo
            $report['total_pending'] += $remainingAmount;

            if ($invoice->due_date->isFuture() || $invoice->due_date->isToday()) {
                $report['current']['count']++;
                $report['current']['total'] += $remainingAmount;
                continue;
            }

            $daysOverdue = $invoice->due_date->diffInDays($now);

            if ($daysOverdue <= 30) {
                $report['1-30']['count']++;
                $report['1-30']['total'] += $remainingAmount;
            } elseif ($daysOverdue <= 60) {
                $report['31-60']['count']++;
                $report['31-60']['total'] += $remainingAmount;
            } elseif ($daysOverdue <= 90) {
                $report['61-90']['count']++;
                $report['61-90']['total'] += $remainingAmount;
            } else {
                $report['over_90']['count']++;
                $report['over_90']['total'] += $remainingAmount;
            }
        }

        return $report;
    }
}
