<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_item_id',
        'movement_type',
        'quantity',
        'previous_quantity',
        'new_quantity',
        'reason',
        'reference',
        'user_id',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'previous_quantity' => 'decimal:2',
        'new_quantity' => 'decimal:2',
    ];

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('movement_type', $type);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isIncrease(): bool
    {
        return in_array($this->movement_type, ['in', 'adjustment']) && $this->quantity > 0;
    }

    public function isDecrease(): bool
    {
        return in_array($this->movement_type, ['out', 'adjustment']) && $this->quantity < 0;
    }
}
