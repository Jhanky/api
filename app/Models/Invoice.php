<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory;

    protected $primaryKey = 'invoice_id';

    protected $fillable = [
        'invoice_number',
        'supplier_id',
        'cost_center_id',
        'amount_before_iva',
        'iva_percentage',
        'total_value',
        'status',
        'payment_type',
        'issue_date',
        'due_date',
        'payment_date',
        'invoice_file_path',
        'invoice_file_name',
        'invoice_file_type',
        'invoice_file_size',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount_before_iva' => 'decimal:2',
        'iva_percentage' => 'decimal:2',
        'iva_amount' => 'decimal:2',
        'total_value' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'invoice_file_size' => 'integer',
    ];

    /**
     * Get the supplier that owns the invoice.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Get the cost center that owns the invoice.
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id', 'cost_center_id');
    }

    /**
     * Get the user who created the invoice.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the invoice.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the payment allocations for the invoice.
     */
    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'invoice_id', 'invoice_id');
    }

    /**
     * Scope to get only pending invoices.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pendiente');
    }

    /**
     * Scope to get only paid invoices.
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'pagada');
    }

    /**
     * Scope to get overdue invoices.
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())->where('status', '!=', 'pagada');
    }

    /**
     * Scope to search invoices by number, supplier name or notes.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('invoice_number', 'like', "%{$search}%")
              ->orWhere('notes', 'like', "%{$search}%")
              ->orWhereHas('supplier', function ($sq) use ($search) {
                  $sq->where('name', 'like', "%{$search}%")
                     ->orWhere('nit', 'like', "%{$search}%");
              });
        });
    }

    /**
     * Scope to filter by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by supplier.
     */
    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    /**
     * Scope to filter by cost center.
     */
    public function scopeByCostCenter($query, $costCenterId)
    {
        return $query->where('cost_center_id', $costCenterId);
    }

    /**
     * Scope to filter by invoice month.
     */
    public function scopeByInvoiceMonth($query, $month)
    {
        return $query->whereMonth('issue_date', $month);
    }

    /**
     * Scope to filter by invoice year.
     */
    public function scopeByInvoiceYear($query, $year)
    {
        return $query->whereYear('issue_date', $year);
    }

    /**
     * Check if the invoice is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'pagada';
    }

    /**
     * Get the total allocated amount.
     */
    public function getTotalAllocatedAttribute(): float
    {
        return $this->paymentAllocations()->sum('allocated_amount');
    }

    /**
     * Get the remaining amount to pay.
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->total_value - $this->total_allocated;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pendiente' => 'Pendiente',
            'pagada' => 'Pagada',
            'parcial' => 'Pago Parcial',
            'anulada' => 'Anulada',
            default => $this->status,
        };
    }
}
