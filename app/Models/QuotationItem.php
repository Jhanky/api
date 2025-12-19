<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'description',
        'category',
        'quantity',
        'unit_measure',
        'unit_price_cop',
        'profit_percentage',
        'display_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price_cop' => 'decimal:2',
        'profit_percentage' => 'decimal:3',
        'display_order' => 'integer',
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
}
