<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAllocation extends Model
{
    use HasFactory;

    protected $primaryKey = 'allocation_id';

    public $timestamps = false;

    protected $fillable = [
        'payment_id',
        'invoice_id',
        'allocated_amount',
        'allocation_date',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'allocation_date' => 'datetime',
    ];

    /**
     * Get the payment that owns the allocation.
     */
    public function payment(): BelongsTo
    {
        return $this->belongsTo(SupplierPayment::class, 'payment_id', 'payment_id');
    }

    /**
     * Get the invoice that the allocation is for.
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'invoice_id');
    }
}
