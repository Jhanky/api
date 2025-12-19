<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'location',
        'address',
        'manager_id',
        'capacity',
        'is_active',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvailableCapacity(): float
    {
        $usedCapacity = $this->inventoryItems()->sum('quantity');
        return $this->capacity - $usedCapacity;
    }
}
