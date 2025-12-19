<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierPayment extends Model
{
    use HasFactory;

    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'payment_code',
        'supplier_id',
        'bank_account_id',
        'payment_amount',
        'payment_date',
        'payment_method',
        'bank_reference',
        'check_number',
        'voucher_file_path',
        'voucher_file_name',
        'voucher_file_type',
        'voucher_file_size',
        'allocation_strategy',
        'notes',
        'created_by',
        'approved_by',
        'approval_date',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'voucher_file_size' => 'integer',
        'approval_date' => 'datetime',
    ];

    /**
     * Get the supplier that owns the payment.
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Get the bank account used for the payment.
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id', 'account_id');
    }

    /**
     * Get the user who created the payment.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who approved the payment.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the payment allocations for this payment.
     */
    public function paymentAllocations(): HasMany
    {
        return $this->hasMany(PaymentAllocation::class, 'payment_id', 'payment_id');
    }

    /**
     * Scope to get only approved payments.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_by');
    }

    /**
     * Scope to get only pending approval payments.
     */
    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_by');
    }

    /**
     * Check if the payment is approved.
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_by);
    }

    /**
     * Get the total allocated amount.
     */
    public function getTotalAllocatedAttribute(): float
    {
        return $this->paymentAllocations()->sum('allocated_amount');
    }

    /**
     * Get the remaining amount to allocate.
     */
    public function getRemainingAmountAttribute(): float
    {
        return $this->payment_amount - $this->total_allocated;
    }

    /**
     * Get the payment method label.
     */
    public function getPaymentMethodLabelAttribute(): string
    {
        return match ($this->payment_method) {
            'efectivo' => 'Efectivo',
            'transferencia' => 'Transferencia',
            'cheque' => 'Cheque',
            'tarjeta' => 'Tarjeta',
            'consignacion' => 'Consignación',
            default => $this->payment_method,
        };
    }
}
