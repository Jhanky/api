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

    // Accessors
    public function getSpecsAttribute(): array
    {
        // Safely decode snapshot_specs, return empty array if invalid
        if (is_array($this->snapshot_specs)) {
            return $this->snapshot_specs;
        }

        if (is_string($this->snapshot_specs)) {
            // Handle empty or null strings
            $trimmed = trim($this->snapshot_specs);
            if (empty($trimmed) || $trimmed === 'null') {
                return [];
            }

            try {
                $decoded = json_decode($this->snapshot_specs, true, 512, JSON_THROW_ON_ERROR);
                return is_array($decoded) ? $decoded : [];
            } catch (\JsonException $e) {
                // Log the error for debugging but don't throw
                \Log::warning('Invalid JSON in snapshot_specs', [
                    'product_id' => $this->id,
                    'snapshot_specs' => $this->snapshot_specs,
                    'error' => $e->getMessage()
                ]);
                return [];
            } catch (\Exception $e) {
                // Fallback for any other error
                \Log::warning('Unexpected error decoding snapshot_specs', [
                    'product_id' => $this->id,
                    'error' => $e->getMessage()
                ]);
                return [];
            }
        }

        return [];
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

    public function getSpec(string $key, $default = null)
    {
        return $this->specs[$key] ?? $default;
    }
}
