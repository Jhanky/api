<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RemissionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'remission_id',
        'material_id',
        'quantity_requested',
        'quantity_dispatched',
        'quantity_received',
        'unit_measure',
        'lot_number',
        'serial_numbers',
        'notes',
        'display_order',
    ];

    protected $casts = [
        'quantity_requested' => 'decimal:2',
        'quantity_dispatched' => 'decimal:2',
        'quantity_received' => 'decimal:2',
        'serial_numbers' => 'array',
        'display_order' => 'integer',
    ];

    /**
     * Get the remission that owns the item.
     */
    public function remission(): BelongsTo
    {
        return $this->belongsTo(Remission::class);
    }

    /**
     * Get the material for this item.
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    /**
     * Check if the item has been fully received.
     */
    public function isFullyReceived(): bool
    {
        return $this->quantity_received >= $this->quantity_requested;
    }

    /**
     * Check if the item has been partially received.
     */
    public function isPartiallyReceived(): bool
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_requested;
    }

    /**
     * Get the remaining quantity to receive.
     */
    public function getRemainingQuantityAttribute(): float
    {
        return $this->quantity_requested - ($this->quantity_received ?? 0);
    }

    /**
     * Get the received percentage.
     */
    public function getReceivedPercentageAttribute(): float
    {
        if ($this->quantity_requested == 0) {
            return 0;
        }
        return (($this->quantity_received ?? 0) / $this->quantity_requested) * 100;
    }
}
