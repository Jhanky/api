<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\CostCenter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Invoice::with(['supplier', 'costCenter']);

            // Búsqueda - ignorar valores undefined o vacíos
            $search = $request->get('search');
            if ($search && $search !== 'undefined' && $search !== 'null') {
                $query->search($search);
            }

            // Filtro por estado - ignorar valores undefined o vacíos
            $status = $request->get('status');
            if ($status && $status !== 'undefined' && $status !== 'null' && $status !== 'all') {
                $query->byStatus($status);
            }

            // Filtro por proveedor
            $supplierId = $request->get('supplier_id');
            if ($supplierId && $supplierId !== 'undefined' && $supplierId !== 'null') {
                $query->bySupplier($supplierId);
            }

            // Filtro por centro de costo
            $costCenterId = $request->get('cost_center_id');
            if ($costCenterId && $costCenterId !== 'undefined' && $costCenterId !== 'null') {
                $query->byCostCenter($costCenterId);
            }

            // Filtro de vencidas
            if ($request->boolean('overdue')) {
                $query->overdue();
            }

            // Filtro por mes/año de la factura
            $invoiceMonth = $request->get('invoice_month');
            if ($invoiceMonth && $invoiceMonth !== 'undefined' && is_numeric($invoiceMonth)) {
                $query->byInvoiceMonth($invoiceMonth);
            }

            $invoiceYear = $request->get('invoice_year');
            if ($invoiceYear && $invoiceYear !== 'undefined' && is_numeric($invoiceYear)) {
                $query->byInvoiceYear($invoiceYear);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'invoice_id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $invoices = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $invoices
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'invoice_number' => 'required|string|max:100',
                'invoice_date' => 'required|date',
                'due_date' => 'nullable|date|after_or_equal:invoice_date',
                'subtotal' => 'required|numeric|min:0',
                'retention' => 'nullable|numeric|min:0',
                'has_retention' => 'nullable|boolean',
                'description' => 'nullable|string|max:1000',
                'status' => 'required|in:PENDIENTE,PAGADA',
                'sale_type' => 'required|in:CONTADO,CREDITO',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'provider_id' => 'required|exists:providers,provider_id',
                'cost_center_id' => 'required|exists:cost_centers,cost_center_id',
                'payment_support' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
                'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240' // 10MB max
            ]);

            $data = $request->all();

            // Manejar subida de archivos
            if ($request->hasFile('payment_support')) {
                $paymentSupportFile = $request->file('payment_support');
                $paymentSupportPath = $paymentSupportFile->store('invoices/payment_support', 'public');
                $data['payment_support'] = $paymentSupportPath;
            }

            if ($request->hasFile('invoice_file')) {
                $invoiceFile = $request->file('invoice_file');
                $invoiceFilePath = $invoiceFile->store('invoices/invoice_files', 'public');
                $data['invoice_file'] = $invoiceFilePath;
            }

            $invoice = Invoice::create($data);
            $invoice->load(['provider', 'costCenter', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Factura creada exitosamente',
                'data' => $invoice
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $invoice = Invoice::with(['provider', 'costCenter'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $invoice
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $request->validate([
                'invoice_number' => 'required|string|max:100',
                'invoice_date' => 'required|date',
                'due_date' => 'nullable|date|after_or_equal:invoice_date',
                'subtotal' => 'required|numeric|min:0',
                'retention' => 'nullable|numeric|min:0',
                'has_retention' => 'nullable|boolean',
                'description' => 'nullable|string|max:1000',
                'status' => 'required|in:PENDIENTE,PAGADA',
                'sale_type' => 'required|in:CONTADO,CREDITO',
                'payment_method_id' => 'nullable|exists:payment_methods,id',
                'provider_id' => 'required|exists:providers,provider_id',
                'cost_center_id' => 'required|exists:cost_centers,cost_center_id',
                'payment_support' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
                'invoice_file' => 'nullable|file|mimes:pdf,jpeg,png|max:10240' // 10MB max
            ]);

            $data = $request->all();

            // Manejar subida de archivos
            if ($request->hasFile('payment_support')) {
                // Eliminar archivo anterior si existe
                if ($invoice->payment_support && \Storage::disk('public')->exists($invoice->payment_support)) {
                    \Storage::disk('public')->delete($invoice->payment_support);
                }
                
                $paymentSupportFile = $request->file('payment_support');
                $paymentSupportPath = $paymentSupportFile->store('invoices/payment_support', 'public');
                $data['payment_support'] = $paymentSupportPath;
            }

            if ($request->hasFile('invoice_file')) {
                // Eliminar archivo anterior si existe
                if ($invoice->invoice_file && \Storage::disk('public')->exists($invoice->invoice_file)) {
                    \Storage::disk('public')->delete($invoice->invoice_file);
                }
                
                $invoiceFile = $request->file('invoice_file');
                $invoiceFilePath = $invoiceFile->store('invoices/invoice_files', 'public');
                $data['invoice_file'] = $invoiceFilePath;
            }

            $invoice->update($data);
            $invoice->load(['provider', 'costCenter', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Factura actualizada exitosamente',
                'data' => $invoice
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);
            $invoice->delete();

            return response()->json([
                'success' => true,
                'message' => 'Factura eliminada exitosamente'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar estado de factura
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $request->validate([
                'status' => 'required|in:PENDIENTE,PAGADA'
            ]);

            $invoice->update(['status' => $request->status]);
            $invoice->load(['provider', 'costCenter', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Estado de factura actualizado exitosamente',
                'data' => $invoice
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar estado de factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Probar consulta de facturas para reporte
     */
    public function testReportQuery(Request $request): JsonResponse
    {
        try {
            // Validar parámetros de filtro
            $request->validate([
                'status' => 'nullable|in:PENDIENTE,PAGADA',
                'provider_id' => 'nullable|exists:providers,provider_id',
                'cost_center_id' => 'nullable|exists:cost_centers,cost_center_id',
                'month' => 'nullable|integer|min:1|max:12',
                'year' => 'nullable|integer|min:2020|max:2030'
            ]);

            // Construir query con filtros
            $query = Invoice::with(['provider', 'costCenter', 'paymentMethod']);

            // Aplicar filtros
            if ($request->has('status') && $request->status) {
                $query->byStatus($request->status);
            }

            if ($request->has('provider_id') && $request->provider_id) {
                $query->byProvider($request->provider_id);
            }

            if ($request->has('cost_center_id') && $request->cost_center_id) {
                $query->byCostCenter($request->cost_center_id);
            }

            if ($request->has('month') && $request->month) {
                $query->byInvoiceMonth($request->month);
            }

            if ($request->has('year') && $request->year) {
                $query->byInvoiceYear($request->year);
            }

            // Obtener facturas
            $invoices = $query->orderBy('invoice_date', 'desc')->get();

            // Preparar datos para respuesta
            $invoiceData = $invoices->map(function ($invoice) {
                return [
                    'id' => $invoice->invoice_id,
                    'number' => $invoice->invoice_number,
                    'date' => $invoice->invoice_date,
                    'amount' => $invoice->total_amount,
                    'status' => $invoice->status,
                    'provider' => $invoice->provider ? $invoice->provider->provider_name : 'Sin proveedor',
                    'cost_center' => $invoice->costCenter ? $invoice->costCenter->cost_center_name : 'Sin centro de costo',
                    'due_date' => $invoice->due_date,
                    'description' => $invoice->description
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $invoices->count(),
                'filters_applied' => $request->all(),
                'data' => $invoiceData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al probar consulta',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de facturas
     */
    public function statistics(): JsonResponse
    {
        try {
            $totalInvoices = Invoice::count();
            $pendingInvoices = Invoice::pending()->count();
            $paidInvoices = Invoice::paid()->count();
            $overdueInvoices = Invoice::overdue()->count();
            $totalAmount = Invoice::sum('total_amount');
            $pendingAmount = Invoice::pending()->sum('total_amount');
            $paidAmount = Invoice::paid()->sum('total_amount');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_invoices' => $totalInvoices,
                    'pending_invoices' => $pendingInvoices,
                    'paid_invoices' => $paidInvoices,
                    'overdue_invoices' => $overdueInvoices,
                    'total_amount' => (float) $totalAmount,
                    'pending_amount' => (float) $pendingAmount,
                    'paid_amount' => (float) $paidAmount
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cambiar centro de costo de una factura
     */
    public function changeCostCenter(Request $request, string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $request->validate([
                'cost_center_id' => 'required|exists:cost_centers,cost_center_id'
            ]);

            $oldCostCenter = $invoice->costCenter;
            $invoice->update(['cost_center_id' => $request->cost_center_id]);
            $invoice->load(['provider', 'costCenter', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Centro de costo actualizado exitosamente',
                'data' => [
                    'invoice' => $invoice,
                    'old_cost_center' => $oldCostCenter,
                    'new_cost_center' => $invoice->costCenter
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cambiar centro de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aplicar o remover retención de una factura
     */
    public function toggleRetention(Request $request, string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $request->validate([
                'has_retention' => 'required|boolean',
                'retention_amount' => 'nullable|numeric|min:0'
            ]);

            $oldRetention = $invoice->retention;
            $oldHasRetention = $invoice->has_retention;

            if ($request->has_retention) {
                $retentionAmount = $request->retention_amount ?? $invoice->retention;
                $invoice->applyRetention($retentionAmount);
                $message = 'Retención aplicada exitosamente';
            } else {
                $invoice->removeRetention();
                $message = 'Retención removida exitosamente';
            }

            $invoice->load(['provider', 'costCenter', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'invoice' => $invoice,
                    'retention_summary' => $invoice->getRetentionSummary(),
                    'changes' => [
                        'old_has_retention' => $oldHasRetention,
                        'new_has_retention' => $invoice->has_retention,
                        'old_retention_amount' => $oldRetention,
                        'new_retention_amount' => $invoice->retention
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar retención',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar reporte de facturas en Excel
     */
    public function generateReport(Request $request)
    {
        try {
            // Validar parámetros de filtro
            $request->validate([
                'status' => 'nullable|in:PENDIENTE,PAGADA',
                'provider_id' => 'nullable|exists:providers,provider_id',
                'cost_center_id' => 'nullable|exists:cost_centers,cost_center_id',
                'month' => 'nullable|integer|min:1|max:12',
                'year' => 'nullable|integer|min:2020|max:2030'
            ]);

            // Construir query con filtros
            $query = Invoice::with(['provider', 'costCenter', 'paymentMethod']);

            // Aplicar filtros
            if ($request->has('status') && $request->status) {
                $query->byStatus($request->status);
            }

            if ($request->has('provider_id') && $request->provider_id) {
                $query->byProvider($request->provider_id);
            }

            if ($request->has('cost_center_id') && $request->cost_center_id) {
                $query->byCostCenter($request->cost_center_id);
            }

            if ($request->has('month') && $request->month) {
                $query->byInvoiceMonth($request->month);
            }

            if ($request->has('year') && $request->year) {
                $query->byInvoiceYear($request->year);
            }

            // Ordenar por fecha de factura
            $invoices = $query->orderBy('invoice_date', 'desc')->get();

            // Log para depuración
            \Log::info('Facturas encontradas para reporte: ' . $invoices->count());
            \Log::info('Filtros aplicados: ' . json_encode($request->all()));

            // Verificar que hay facturas
            if ($invoices->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron facturas con los filtros aplicados',
                    'filters_applied' => $request->all()
                ], 404);
            }

            // Generar nombre del archivo
            $fileName = $this->generateFileName($request);
            
            // Generar contenido Excel
            $excelContent = $this->generateExcelContent($invoices);
            
            // Retornar archivo como descarga
            return response($excelContent)
                ->header('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
                ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generar nombre del archivo basado en los filtros
     */
    private function generateFileName(Request $request): string
    {
        $parts = ['reporte_facturas'];
        
        if ($request->status) {
            $parts[] = strtolower($request->status);
        }
        
        if ($request->provider_id) {
            $provider = Provider::find($request->provider_id);
            if ($provider) {
                $parts[] = 'proveedor_' . Str::slug($provider->provider_name);
            }
        }
        
        if ($request->cost_center_id) {
            $costCenter = CostCenter::find($request->cost_center_id);
            if ($costCenter) {
                $parts[] = 'centro_' . Str::slug($costCenter->cost_center_name);
            }
        }
        
        if ($request->month && $request->year) {
            $parts[] = $request->year . '_' . str_pad($request->month, 2, '0', STR_PAD_LEFT);
        } elseif ($request->year) {
            $parts[] = $request->year;
        }
        
        $parts[] = now()->format('Y-m-d_H-i-s');
        
        return implode('_', $parts) . '.xlsx';
    }

    /**
     * Generar contenido Excel
     */
    private function generateExcelContent($invoices): string
    {
        try {
            // Verificar que hay facturas para procesar
            if ($invoices->isEmpty()) {
                throw new \Exception('No se encontraron facturas con los filtros aplicados');
            }

            // Crear nueva instancia de Spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Configurar título de la hoja
            $sheet->setTitle('Reporte de Facturas');
            
            // Definir encabezados según la estructura requerida
            $headers = [
                'A1' => 'Número',
                'B1' => 'Fecha',
                'C1' => 'antes de iva(Subtotal)',
                'D1' => 'IVA',
                'E1' => 'Aplica Retención',
                'F1' => 'Valor Pagado',
                'G1' => 'Estado',
                'H1' => 'Tipo de Compra',
                'I1' => 'Proveedor',
                'J1' => 'Centro de Costo',
                'K1' => 'Fecha Vencimiento',
                'L1' => 'Metodo de pago',
                'M1' => 'Descripción',
                'N1' => 'Soporte de pago',
                'O1' => 'Factura'
            ];
            
            // Escribir encabezados
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Estilo para encabezados
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '366092']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ];
            
            $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);
            
            // Ajustar ancho de columnas
            $sheet->getColumnDimension('A')->setWidth(15); // Número
            $sheet->getColumnDimension('B')->setWidth(12); // Fecha
            $sheet->getColumnDimension('C')->setWidth(18); // antes de iva(Subtotal)
            $sheet->getColumnDimension('D')->setWidth(12); // IVA
            $sheet->getColumnDimension('E')->setWidth(15); // Aplica Retención
            $sheet->getColumnDimension('F')->setWidth(15); // Valor Pagado
            $sheet->getColumnDimension('G')->setWidth(12); // Estado
            $sheet->getColumnDimension('H')->setWidth(15); // Tipo de Compra
            $sheet->getColumnDimension('I')->setWidth(25); // Proveedor
            $sheet->getColumnDimension('J')->setWidth(20); // Centro de Costo
            $sheet->getColumnDimension('K')->setWidth(15); // Fecha Vencimiento
            $sheet->getColumnDimension('L')->setWidth(15); // Metodo de pago
            $sheet->getColumnDimension('M')->setWidth(30); // Descripción
            $sheet->getColumnDimension('N')->setWidth(40); // Soporte de pago
            $sheet->getColumnDimension('O')->setWidth(40); // Factura
            
            // Escribir datos
            $row = 2;
            foreach ($invoices as $invoice) {
                try {
                    // Validar que la factura existe y tiene datos básicos
                    if (!$invoice) {
                        continue;
                    }

                    // Datos básicos
                    $sheet->setCellValue('A' . $row, $invoice->invoice_number ?? '');
                    $sheet->setCellValue('B' . $row, $invoice->invoice_date ? $invoice->invoice_date->format('d/m/Y') : '');
                    
                    // Valores contables
                    $sheet->setCellValue('C' . $row, $invoice->subtotal ?? 0);
                    $sheet->setCellValue('D' . $row, $invoice->iva_amount ?? 0);
                    
                    // Retención como Sí/No
                    $retentionText = 'No';
                    if ($invoice->has_retention && $invoice->retention > 0) {
                        $retentionText = 'Sí';
                    }
                    $sheet->setCellValue('E' . $row, $retentionText);
                    
                    $sheet->setCellValue('F' . $row, $invoice->total_amount ?? 0);
                    $sheet->setCellValue('G' . $row, $invoice->status ?? '');
                    
                    // Tipo de compra (sale_type)
                    $saleTypeText = '';
                    if ($invoice->sale_type) {
                        $saleTypeText = $invoice->sale_type === 'CONTADO' ? 'Contado' : 'Crédito';
                    }
                    $sheet->setCellValue('H' . $row, $saleTypeText);
                    
                    // Relaciones
                    $providerName = '';
                    if ($invoice->provider) {
                        $providerName = $invoice->provider->provider_name ?? '';
                    }
                    $sheet->setCellValue('I' . $row, $providerName);
                    
                    $costCenterName = '';
                    if ($invoice->costCenter) {
                        $costCenterName = $invoice->costCenter->cost_center_name ?? '';
                    }
                    $sheet->setCellValue('J' . $row, $costCenterName);
                    
                    // Fechas y métodos
                    $sheet->setCellValue('K' . $row, $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '');
                    $sheet->setCellValue('L' . $row, $invoice->payment_method ?? '');
                    $sheet->setCellValue('M' . $row, $invoice->description ?? '');
                    
                    // URLs de documentos
                    $paymentSupportUrl = '';
                    if ($invoice->payment_support) {
                        $paymentSupportUrl = url('storage/' . $invoice->payment_support);
                    }
                    $sheet->setCellValue('N' . $row, $paymentSupportUrl);
                    
                    $invoiceFileUrl = '';
                    if ($invoice->invoice_file) {
                        $invoiceFileUrl = url('storage/' . $invoice->invoice_file);
                    }
                    $sheet->setCellValue('O' . $row, $invoiceFileUrl);
                    
                    // Formato para columnas numéricas
                    $sheet->getStyle('C' . $row)->getNumberFormat()->setFormatCode('#,##0.00'); // Subtotal
                    $sheet->getStyle('D' . $row)->getNumberFormat()->setFormatCode('#,##0.00'); // IVA
                    $sheet->getStyle('F' . $row)->getNumberFormat()->setFormatCode('#,##0.00'); // Valor Pagado
                    
                    // Colorear estado
                    if ($invoice->status === 'PAGADA') {
                        $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('008000'); // Verde
                    } else {
                        $sheet->getStyle('G' . $row)->getFont()->getColor()->setRGB('FF0000'); // Rojo
                    }
                    
                    // Colorear tipo de compra
                    if ($invoice->sale_type === 'CONTADO') {
                        $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('0066CC'); // Azul para Contado
                    } else {
                        $sheet->getStyle('H' . $row)->getFont()->getColor()->setRGB('FF6600'); // Naranja para Crédito
                    }
                    
                    // Colorear retención
                    if ($retentionText === 'Sí') {
                        $sheet->getStyle('E' . $row)->getFont()->getColor()->setRGB('CC0000'); // Rojo para Sí
                    } else {
                        $sheet->getStyle('E' . $row)->getFont()->getColor()->setRGB('666666'); // Gris para No
                    }
                    
                    $row++;
                } catch (\Exception $e) {
                    // Log del error pero continuar con la siguiente factura
                    \Log::error('Error procesando factura ID: ' . ($invoice->invoice_id ?? 'desconocido') . ' - ' . $e->getMessage());
                    continue;
                }
            }
            
            // Verificar que se escribieron datos
            if ($row <= 2) {
                throw new \Exception('No se pudieron procesar las facturas correctamente');
            }
            
            // Aplicar bordes a todos los datos
            $lastRow = $row - 1;
            if ($lastRow > 1) {
                $sheet->getStyle('A1:O' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);
            }
            
            // Congelar la primera fila
            $sheet->freezePane('A2');
            
            // Generar archivo Excel
            $writer = new Xlsx($spreadsheet);
            
            // Capturar el contenido en un string
            ob_start();
            $writer->save('php://output');
            $excelContent = ob_get_contents();
            ob_end_clean();
            
            return $excelContent;
            
        } catch (\Exception $e) {
            \Log::error('Error generando Excel: ' . $e->getMessage());
            throw new \Exception('Error al generar el archivo Excel: ' . $e->getMessage());
        }
    }

    /**
     * Upload files to an invoice
     */
    public function uploadFiles(Request $request, string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $request->validate([
                'payment_support' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240', // 10MB max
                'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240' // 10MB max
            ]);

            $uploadedFiles = [];

            // Manejar subida de soporte de pago
            if ($request->hasFile('payment_support')) {
                // Eliminar archivo anterior si existe
                if ($invoice->payment_support && \Storage::disk('public')->exists($invoice->payment_support)) {
                    \Storage::disk('public')->delete($invoice->payment_support);
                }
                
                $paymentSupportFile = $request->file('payment_support');
                $paymentSupportPath = $paymentSupportFile->store('invoices/payment_support', 'public');
                $invoice->update(['payment_support' => $paymentSupportPath]);
                $uploadedFiles['payment_support'] = [
                    'path' => $paymentSupportPath,
                    'url' => url('storage/' . $paymentSupportPath),
                    'size' => $paymentSupportFile->getSize(),
                    'original_name' => $paymentSupportFile->getClientOriginalName()
                ];
            }

            // Manejar subida de archivo de factura
            if ($request->hasFile('invoice_file')) {
                // Eliminar archivo anterior si existe
                if ($invoice->invoice_file && \Storage::disk('public')->exists($invoice->invoice_file)) {
                    \Storage::disk('public')->delete($invoice->invoice_file);
                }
                
                $invoiceFile = $request->file('invoice_file');
                $invoiceFilePath = $invoiceFile->store('invoices/invoice_files', 'public');
                $invoice->update(['invoice_file' => $invoiceFilePath]);
                $uploadedFiles['invoice_file'] = [
                    'path' => $invoiceFilePath,
                    'url' => url('storage/' . $invoiceFilePath),
                    'size' => $invoiceFile->getSize(),
                    'original_name' => $invoiceFile->getClientOriginalName()
                ];
            }

            $invoice->load(['provider', 'costCenter', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Archivos subidos exitosamente',
                'data' => [
                    'invoice' => $invoice,
                    'uploaded_files' => $uploadedFiles,
                    'file_urls' => [
                        'payment_support_url' => $invoice->payment_support ? url('storage/' . $invoice->payment_support) : null,
                        'invoice_file_url' => $invoice->invoice_file ? url('storage/' . $invoice->invoice_file) : null
                    ]
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove files from an invoice
     */
    public function removeFiles(Request $request, string $id): JsonResponse
    {
        try {
            $invoice = Invoice::findOrFail($id);

            $request->validate([
                'file_type' => 'required|in:payment_support,invoice_file,both'
            ]);

            $removedFiles = [];

            if ($request->file_type === 'payment_support' || $request->file_type === 'both') {
                if ($invoice->payment_support && \Storage::disk('public')->exists($invoice->payment_support)) {
                    \Storage::disk('public')->delete($invoice->payment_support);
                    $removedFiles[] = 'payment_support';
                }
                $invoice->update(['payment_support' => null]);
            }

            if ($request->file_type === 'invoice_file' || $request->file_type === 'both') {
                if ($invoice->invoice_file && \Storage::disk('public')->exists($invoice->invoice_file)) {
                    \Storage::disk('public')->delete($invoice->invoice_file);
                    $removedFiles[] = 'invoice_file';
                }
                $invoice->update(['invoice_file' => null]);
            }

            $invoice->load(['provider', 'costCenter', 'paymentMethod']);

            return response()->json([
                'success' => true,
                'message' => 'Archivos eliminados exitosamente',
                'data' => [
                    'invoice' => $invoice,
                    'removed_files' => $removedFiles
                ]
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a new invoice with automatic supplier creation.
     * If supplier NIT exists, uses existing supplier.
     * If not, creates a new supplier and then creates the invoice.
     */
    public function storeWithSupplier(Request $request): JsonResponse
    {
        try {
            $request->validate([
                // Datos del proveedor
                'supplier_name' => 'required|string|max:255',
                'supplier_nit' => 'required|string|max:50',
                // Datos de la factura
                'invoice_number' => 'required|string|max:100',
                'invoice_date' => 'required|date',
                'due_date' => 'nullable|date',
                'subtotal' => 'nullable|numeric|min:0',
                'iva_amount' => 'nullable|numeric|min:0',
                'total_amount' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:1000',
                'cost_center_id' => 'required|exists:cost_centers,cost_center_id',
                'invoice_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp|max:10240' // 10MB max
            ]);

            \DB::beginTransaction();

            // 1. Buscar o crear proveedor por NIT
            $supplier = Supplier::where('nit', $request->supplier_nit)->first();
            
            if (!$supplier) {
                $supplier = Supplier::create([
                    'name' => $request->supplier_name,
                    'nit' => $request->supplier_nit,
                    'is_active' => true
                ]);
                \Log::info('Nuevo proveedor creado: ' . $supplier->name . ' (NIT: ' . $supplier->nit . ')');
            }

            // 2. Preparar datos de la factura
            $invoiceData = [
                'invoice_number' => $request->invoice_number,
                'issue_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'amount_before_iva' => $request->subtotal ?? 0,
                'iva_percentage' => $request->iva_percentage ?? 19,
                'total_value' => $request->total_amount,
                'notes' => $request->description,
                'status' => 'pendiente',
                'payment_type' => 'total',
                'supplier_id' => $supplier->supplier_id,
                'cost_center_id' => $request->cost_center_id,
                'created_by' => auth()->id() ?? 1
            ];

            // 3. Manejar archivo de factura
            if ($request->hasFile('invoice_file')) {
                $invoiceFile = $request->file('invoice_file');
                $invoiceFilePath = $invoiceFile->store('invoices/invoice_files', 'public');
                $invoiceData['invoice_file_path'] = $invoiceFilePath;
                $invoiceData['invoice_file_name'] = $invoiceFile->getClientOriginalName();
                $invoiceData['invoice_file_type'] = $invoiceFile->getClientMimeType();
                $invoiceData['invoice_file_size'] = $invoiceFile->getSize();
                \Log::info('Archivo de factura guardado: ' . $invoiceFilePath);
            }

            // 4. Crear factura
            $invoice = Invoice::create($invoiceData);
            $invoice->load(['supplier', 'costCenter']);

            \DB::commit();

            \Log::info('Factura creada exitosamente: ' . $invoice->invoice_number);

            return response()->json([
                'success' => true,
                'message' => 'Factura registrada exitosamente',
                'data' => [
                    'invoice' => $invoice,
                    'supplier' => $supplier,
                    'supplier_created' => !Supplier::where('nit', $request->supplier_nit)->where('supplier_id', '!=', $supplier->supplier_id)->exists()
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Error al crear factura con proveedor: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar factura',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all cost centers and projects for unified dropdown.
     */
    public function getCostCentersAndProjects(): JsonResponse
    {
        try {
            // Obtener todos los centros de costo activos
            $costCenters = CostCenter::where('is_active', true)
                ->orderBy('name')
                ->get()
                ->map(function ($cc) {
                    return [
                        'id' => $cc->cost_center_id,
                        'name' => $cc->name,
                        'code' => $cc->code,
                        'label' => ($cc->code ? $cc->code . ' - ' : '') . $cc->name
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'cost_centers' => $costCenters
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al obtener centros de costo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener centros de costo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download the invoice file.
     */
    public function downloadFile($id)
    {
        try {
            $invoice = Invoice::findOrFail($id);
            
            if (!$invoice->invoice_file_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Esta factura no tiene archivo adjunto'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $invoice->invoice_file_path);
            
            if (!file_exists($filePath)) {
                \Log::error('Archivo no encontrado: ' . $filePath);
                return response()->json([
                    'success' => false,
                    'message' => 'Archivo no encontrado en el servidor'
                ], 404);
            }

            $fileName = $invoice->invoice_file_name ?: basename($invoice->invoice_file_path);
            $mimeType = $invoice->invoice_file_type ?: mime_content_type($filePath);

            return response()->file($filePath, [
                'Content-Type' => $mimeType,
                'Content-Disposition' => 'inline; filename="' . $fileName . '"'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Factura no encontrada'
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error al descargar archivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar archivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
