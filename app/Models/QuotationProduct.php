<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'product_type',
        'product_id',
        'snapshot_brand',
        'snapshot_model',
        'snapshot_specs',
        'quantity',
        'unit_price_cop',
        'profit_percentage',
        'display_order',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'quantity' => 'decimal:2',
        'unit_price_cop' => 'decimal:2',
        'profit_percentage' => 'decimal:3',
        'display_order' => 'integer',
        'snapshot_specs' => 'array',
    ];

    // Relationships
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    // Scopes
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('product_type', $type);
    }

    // Helper methods
    public function getSubtotal(): float
    {
        return $this->quantity * $this->unit_price_cop;
    }

    public function getSubtotalWithProfit(): float
    {
        $subtotal = $this->getSubtotal();
        if ($this->profit_percentage) {
            return $subtotal * (1 + ($this->profit_percentage / 100));
        }
        return $subtotal;
    }

    public function isPanel(): bool
    {
        return $this->product_type === 'panel';
    }

    public function isInverter(): bool
    {
        return $this->product_type === 'inverter';
    }

    public function isBattery(): bool
    {
        return $this->product_type === 'battery';
    }
}
