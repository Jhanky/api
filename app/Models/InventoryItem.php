<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id',
        'warehouse_id',
        'quantity',
        'min_stock',
        'max_stock',
        'location',
        'batch_number',
        'expiry_date',
        'unit_cost',
        'total_value',
        'is_active',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'max_stock' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total_value' => 'decimal:2',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByWarehouse($query, $warehouseId)
    {
        return $query->where('warehouse_id', $warehouseId);
    }

    public function scopeLowStock($query)
    {
        return $query->whereRaw('quantity <= min_stock');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= ($this->min_stock ?? 0);
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function updateTotalValue(): void
    {
        $this->total_value = $this->quantity * $this->unit_cost;
        $this->save();
    }
}
