<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inverter extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'power',
        'system_type',
        'grid_type',
        'price',
        'technical_sheet_url',
    ];

    protected $casts = [
        'power' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    public function scopeBySystemType($query, $systemType)
    {
        return $query->where('system_type', $systemType);
    }

    public function scopeByGridType($query, $gridType)
    {
        return $query->where('grid_type', $gridType);
    }

    public function scopeByPowerRange($query, $minPower, $maxPower)
    {
        if ($minPower && $maxPower) {
            return $query->whereBetween('power', [$minPower, $maxPower]);
        } elseif ($minPower) {
            return $query->where('power', '>=', $minPower);
        } elseif ($maxPower) {
            return $query->where('power', '<=', $maxPower);
        }
        return $query;
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        if ($minPrice && $maxPrice) {
            return $query->whereBetween('price', [$minPrice, $maxPrice]);
        } elseif ($minPrice) {
            return $query->where('price', '>=', $minPrice);
        } elseif ($maxPrice) {
            return $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }

    public function getFullName(): string
    {
        return $this->brand . ' ' . $this->model . ' (' . $this->power . 'W)';
    }

    public function getPowerKw(): float
    {
        return $this->power / 1000;
    }
}
