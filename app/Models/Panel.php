<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Panel extends Model
{
    use HasFactory;

    protected $fillable = [
        'brand',
        'model',
        'power',
        'price',
        'technical_sheet_url',
    ];

    protected $casts = [
        'power' => 'decimal:2',
        'price' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    public function scopeByPowerRange($query, $minPower, $maxPower)
    {
        return $query->whereBetween('power', [$minPower, $maxPower]);
    }

    public function getFullName(): string
    {
        return $this->brand . ' ' . $this->model . ' (' . $this->power . ' W)';
    }

    public function getPowerKw(): float
    {
        return $this->power / 1000;
    }
}
