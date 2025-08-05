<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Panel extends Model
{
    use HasFactory;

    protected $primaryKey = 'panel_id';
    
    protected $fillable = [
        'brand',
        'model',
        'power',
        'type',
        'technical_sheet_url',
        'price'
    ];

    protected $casts = [
        'power' => 'float',
        'price' => 'float',
    ];

    // Accessor para obtener la URL completa del archivo PDF
    public function getTechnicalSheetUrlAttribute($value)
    {
        if ($value && Storage::disk('public')->exists($value)) {
            return asset('storage/' . $value);
        }
        return $value;
    }

    // Scope para filtrar por marca
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', 'like', '%' . $brand . '%');
    }

    // Scope para filtrar por tipo
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Scope para filtrar por rango de potencia
    public function scopeByPowerRange($query, $minPower = null, $maxPower = null)
    {
        if ($minPower) {
            $query->where('power', '>=', $minPower);
        }
        if ($maxPower) {
            $query->where('power', '<=', $maxPower);
        }
        return $query;
    }

    // Scope para filtrar por rango de precio
    public function scopeByPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }
}
