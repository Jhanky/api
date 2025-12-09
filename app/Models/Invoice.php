<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        // Información básica de la factura
        'invoice_number',
        'invoice_date',
        'due_date',

        // Información del proveedor y centro de costos
        'provider_id',
        'cost_center_id',

        // Valores contables (orden lógico contable)
        'subtotal',           // Subtotal antes de impuestos
        'iva_amount',         // Valor del IVA (19% del subtotal)
        'retention',         // Retención en la fuente
        'has_retention',     // Indica si aplica retención
        'total_amount',      // Total a pagar (subtotal + IVA - retención)

        // Estado y método de pago
        'status',
        'sale_type',          // Tipo de venta: CONTADO o CREDITO
        'payment_method_id',  // ID del método de pago

        // Documentos y archivos
        'payment_support',   // Archivo de soporte de pago
        'invoice_file',      // Archivo de la factura

        // Descripción y metadatos
        'description'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'total_amount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'retention' => 'decimal:2',
        'has_retention' => 'boolean'
    ];

    // ========================================
    // CONSTANTES DE MÉTODOS DE PAGO
    // ========================================
    
    const PAYMENT_METHOD_TCD = 'Transferencia desde cuenta Davivienda E4(TCD)';
    const PAYMENT_METHOD_CP = 'Transferencia desde Cuenta personal(CP)';
    const PAYMENT_METHOD_EF = 'Efectivo(EF)';
    
    /**
     * Obtener todos los métodos de pago disponibles
     */
    public static function getPaymentMethods()
    {
        return [
            self::PAYMENT_METHOD_TCD => 'Transferencia desde cuenta Davivienda E4(TCD)',
            self::PAYMENT_METHOD_CP => 'Transferencia desde Cuenta personal(CP)',
            self::PAYMENT_METHOD_EF => 'Efectivo(EF)'
        ];
    }

    // Relaciones
    public function provider()
    {
        return $this->belongsTo(Provider::class, 'provider_id', 'provider_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id', 'cost_center_id');
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    // ========================================
    // SCOPES BÁSICOS DE FILTROS
    // ========================================
    
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByProvider($query, $providerId)
    {
        return $query->where('provider_id', $providerId);
    }

    public function scopeByCostCenter($query, $costCenterId)
    {
        return $query->where('cost_center_id', $costCenterId);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('invoice_number', 'like', '%' . $search . '%')
              ->orWhereHas('provider', function($providerQuery) use ($search) {
                  $providerQuery->where('provider_name', 'like', '%' . $search . '%');
              })
              ->orWhereHas('costCenter', function($costCenterQuery) use ($search) {
                  $costCenterQuery->where('cost_center_name', 'like', '%' . $search . '%');
              });
        });
    }

    public function scopeByInvoiceMonth($query, $month)
    {
        return $query->whereMonth('invoice_date', (int) $month);
    }

    public function scopeByInvoiceYear($query, $year)
    {
        return $query->whereYear('invoice_date', (int) $year);
    }

    // ========================================
    // MÉTODOS CONTABLES Y CÁLCULOS
    // ========================================
    
    /**
     * Calcula el valor del IVA (19% del subtotal)
     * @return float
     */
    public function calculateIvaAmount()
    {
        if ($this->subtotal) {
            return $this->subtotal * 0.19; // 19% de IVA
        }
        return 0;
    }

    /**
     * Calcula el total a pagar (subtotal + IVA - retención)
     * @return float
     */
    public function calculateTotalAmount()
    {
        $subtotal = $this->subtotal ?? 0;
        $iva = $this->iva_amount ?? $this->calculateIvaAmount();
        $retention = $this->retention ?? 0;
        
        return $subtotal + $iva - $retention;
    }

    /**
     * Obtiene el resumen contable de la factura
     * @return array
     */
    public function getAccountingSummary()
    {
        return [
            'subtotal' => $this->subtotal ?? 0,
            'iva_amount' => $this->iva_amount ?? $this->calculateIvaAmount(),
            'retention' => $this->retention ?? 0,
            'total_amount' => $this->total_amount ?? $this->calculateTotalAmount(),
            'net_amount' => ($this->subtotal ?? 0) - ($this->retention ?? 0)
        ];
    }

    /**
     * Verifica si la factura está vencida
     * @return bool
     */
    public function isOverdue()
    {
        return $this->due_date < now()->toDateString() && $this->status === 'PENDIENTE';
    }

    /**
     * Calcula los días de vencimiento
     * @return int
     */
    public function getDaysOverdue()
    {
        if ($this->isOverdue()) {
            return now()->diffInDays($this->due_date);
        }
        return 0;
    }

    // Mutator para calcular automáticamente el IVA cuando se actualiza el subtotal
    public function setSubtotalAttribute($value)
    {
        $this->attributes['subtotal'] = $value;
        if ($value) {
            $this->attributes['iva_amount'] = $value * 0.19;
        }
    }

    // Scope para filtrar por método de pago
    public function scopeByPaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    // Scope para facturas con soporte de pago
    public function scopeWithPaymentSupport($query)
    {
        return $query->whereNotNull('payment_support');
    }

    // Scope para facturas con archivo de factura
    public function scopeWithInvoiceFile($query)
    {
        return $query->whereNotNull('invoice_file');
    }

    // ========================================
    // SCOPES CONTABLES ADICIONALES
    // ========================================
    
    /**
     * Scope para facturas con retención
     */
    public function scopeWithRetention($query)
    {
        return $query->where('retention', '>', 0);
    }

    /**
     * Scope para facturas sin retención
     */
    public function scopeWithoutRetention($query)
    {
        return $query->where(function($q) {
            $q->whereNull('retention')->orWhere('retention', 0);
        });
    }

    /**
     * Scope para facturas por rango de montos
     */
    public function scopeByAmountRange($query, $min, $max)
    {
        return $query->whereBetween('total_amount', [$min, $max]);
    }

    /**
     * Scope para facturas con IVA
     */
    public function scopeWithIva($query)
    {
        return $query->where('iva_amount', '>', 0);
    }

    /**
     * Scope para facturas exentas de IVA
     */
    public function scopeExemptFromIva($query)
    {
        return $query->where(function($q) {
            $q->whereNull('iva_amount')->orWhere('iva_amount', 0);
        });
    }

    /**
     * Scope para facturas por período contable
     */
    public function scopeByAccountingPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('invoice_date', [$startDate, $endDate]);
    }

    /**
     * Scope para facturas pendientes de pago
     */
    public function scopePending($query)
    {
        return $query->where('status', 'PENDIENTE');
    }

    /**
     * Scope para facturas pagadas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'PAGADA');
    }

    /**
     * Scope para facturas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->toDateString())
                    ->where('status', 'PENDIENTE');
    }

    /**
     * Scope para facturas próximas a vencer (próximos 7 días)
     */
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('due_date', '<=', now()->addDays($days)->toDateString())
                    ->where('due_date', '>=', now()->toDateString())
                    ->where('status', 'PENDIENTE');
    }

    // ========================================
    // MÉTODOS PARA TIPOS DE VENTA
    // ========================================
    
    /**
     * Scope para facturas de contado
     */
    public function scopeCashSales($query)
    {
        return $query->where('sale_type', 'CONTADO');
    }

    /**
     * Scope para facturas a crédito
     */
    public function scopeCreditSales($query)
    {
        return $query->where('sale_type', 'CREDITO');
    }

    /**
     * Scope para facturas a crédito pendientes de pago
     */
    public function scopeCreditPending($query)
    {
        return $query->where('sale_type', 'CREDITO')
                    ->where('status', 'PENDIENTE');
    }

    /**
     * Scope para facturas a crédito ya pagadas
     */
    public function scopeCreditPaid($query)
    {
        return $query->where('sale_type', 'CREDITO')
                    ->where('status', 'PAGADA');
    }

    /**
     * Scope para facturas de contado pagadas
     */
    public function scopeCashPaid($query)
    {
        return $query->where('sale_type', 'CONTADO')
                    ->where('status', 'PAGADA');
    }

    /**
     * Verifica si la factura es de contado
     */
    public function isCashSale()
    {
        return $this->sale_type === 'CONTADO';
    }

    /**
     * Verifica si la factura es a crédito
     */
    public function isCreditSale()
    {
        return $this->sale_type === 'CREDITO';
    }

    /**
     * Verifica si una factura a crédito ya fue pagada
     */
    public function isCreditPaid()
    {
        return $this->isCreditSale() && $this->status === 'PAGADA';
    }

    /**
     * Verifica si una factura a crédito está pendiente
     */
    public function isCreditPending()
    {
        return $this->isCreditSale() && $this->status === 'PENDIENTE';
    }

    /**
     * Obtiene el resumen de tipos de venta
     */
    public function getSaleTypeSummary()
    {
        return [
            'sale_type' => $this->sale_type,
            'status' => $this->status,
            'is_cash' => $this->isCashSale(),
            'is_credit' => $this->isCreditSale(),
            'is_paid' => $this->status === 'PAGADA',
            'is_pending' => $this->status === 'PENDIENTE',
            'description' => $this->getSaleTypeDescription()
        ];
    }

    /**
     * Obtiene la descripción del tipo de venta
     */
    public function getSaleTypeDescription()
    {
        if ($this->isCashSale()) {
            return $this->status === 'PAGADA' ? 'Venta de Contado (Pagada)' : 'Venta de Contado (Pendiente)';
        } else {
            return $this->status === 'PAGADA' ? 'Venta a Crédito (Pagada)' : 'Venta a Crédito (Pendiente)';
        }
    }

    // ========================================
    // MÉTODOS DE VERIFICACIÓN DE PAGO
    // ========================================
    
    /**
     * Verifica si el pago es por transferencia Davivienda
     */
    public function isTcdPayment()
    {
        return $this->paymentMethod && $this->paymentMethod->code === 'TCD';
    }

    /**
     * Verifica si el pago es por transferencia personal
     */
    public function isCpPayment()
    {
        return $this->paymentMethod && $this->paymentMethod->code === 'CP';
    }

    /**
     * Verifica si el pago es en efectivo
     */
    public function isEfPayment()
    {
        return $this->paymentMethod && $this->paymentMethod->code === 'EF';
    }

    /**
     * Verifica si el pago es por transferencia (cualquiera)
     */
    public function isTransferPayment()
    {
        return $this->isTcdPayment() || $this->isCpPayment();
    }

    /**
     * Obtiene la descripción corta del método de pago
     */
    public function getPaymentMethodShort()
    {
        return $this->paymentMethod ? $this->paymentMethod->code : 'N/A';
    }

    /**
     * Obtiene el nombre del método de pago
     */
    public function getPaymentMethodName()
    {
        return $this->paymentMethod ? $this->paymentMethod->name : 'No especificado';
    }

    /**
     * Obtiene el resumen completo del método de pago
     */
    public function getPaymentMethodSummary()
    {
        if (!$this->paymentMethod) {
            return [
                'method' => null,
                'name' => 'No especificado',
                'short' => 'N/A',
                'is_transfer' => false,
                'is_cash' => false,
                'is_tcd' => false,
                'is_cp' => false
            ];
        }

        return [
            'method' => $this->paymentMethod->name,
            'name' => $this->paymentMethod->name,
            'short' => $this->paymentMethod->code,
            'is_transfer' => $this->isTransferPayment(),
            'is_cash' => $this->isEfPayment(),
            'is_tcd' => $this->isTcdPayment(),
            'is_cp' => $this->isCpPayment()
        ];
    }

    // ========================================
    // MÉTODOS DE RETENCIÓN
    // ========================================
    
    /**
     * Verifica si la factura tiene retención aplicada
     */
    public function hasRetentionApplied()
    {
        return $this->has_retention;
    }

    /**
     * Aplica retención a la factura
     */
    public function applyRetention($retentionAmount = null)
    {
        $this->has_retention = true;
        
        if ($retentionAmount !== null) {
            $this->retention = $retentionAmount;
        }
        
        $this->save();
        $this->calculateTotalAmount();
    }

    /**
     * Remueve la retención de la factura
     */
    public function removeRetention()
    {
        $this->has_retention = false;
        $this->retention = 0;
        $this->save();
        $this->calculateTotalAmount();
    }

    /**
     * Calcula el total considerando la retención
     */
    public function calculateTotalWithRetention()
    {
        if ($this->has_retention && $this->retention > 0) {
            return $this->subtotal + $this->iva_amount - $this->retention;
        }
        
        return $this->subtotal + $this->iva_amount;
    }

    /**
     * Obtiene el resumen de retención
     */
    public function getRetentionSummary()
    {
        return [
            'has_retention' => $this->has_retention,
            'retention_amount' => $this->retention,
            'subtotal' => $this->subtotal,
            'iva_amount' => $this->iva_amount,
            'total_without_retention' => $this->subtotal + $this->iva_amount,
            'total_with_retention' => $this->calculateTotalWithRetention(),
            'retention_percentage' => $this->has_retention && $this->subtotal > 0 
                ? round(($this->retention / $this->subtotal) * 100, 2) 
                : 0
        ];
    }
}
