<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Battery extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'capacity',
        'voltage',
        'type',
        'price',
        'technical_sheet_url',
    ];

    protected $casts = [
        'capacity' => 'decimal:2',
        'voltage' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCapacityRange($query, $minCapacity, $maxCapacity)
    {
        if ($minCapacity && $maxCapacity) {
            return $query->whereBetween('capacity', [$minCapacity, $maxCapacity]);
        } elseif ($minCapacity) {
            return $query->where('capacity', '>=', $minCapacity);
        } elseif ($maxCapacity) {
            return $query->where('capacity', '<=', $maxCapacity);
        }
        return $query;
    }

    public function scopeByVoltageRange($query, $minVoltage, $maxVoltage)
    {
        if ($minVoltage && $maxVoltage) {
            return $query->whereBetween('voltage', [$minVoltage, $maxVoltage]);
        } elseif ($minVoltage) {
            return $query->where('voltage', '>=', $minVoltage);
        } elseif ($maxVoltage) {
            return $query->where('voltage', '<=', $maxVoltage);
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
        return $this->brand . ' ' . $this->model . ' (' . $this->capacity . 'Ah)';
    }
}
