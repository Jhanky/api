<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Material extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'material_category_id',
        'unit',
        'specifications',
        'is_active',
    ];

    protected $casts = [
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(MaterialCategory::class, 'material_category_id');
    }

    public function inventoryItems(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('material_category_id', $categoryId);
    }

    public function getTotalStock(): float
    {
        return $this->inventoryItems()->sum('quantity');
    }

    public function getAvailableStock(): float
    {
        return $this->inventoryItems()->where('is_active', true)->sum('quantity');
    }
}
