<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Battery extends Model
{
    use HasFactory;

    protected $primaryKey = 'battery_id';
    
    protected $fillable = [
        'brand',
        'model', 
        'capacity',
        'voltage',
        'type',
        'technical_sheet_url',
        'price'
    ];

    protected $casts = [
        'capacity' => 'float',
        'voltage' => 'float',
        'price' => 'float',
    ];

    // Accessor para obtener la URL completa del archivo
    public function getTechnicalSheetUrlAttribute($value)
    {
        return $value ? Storage::url($value) : null;
    }

    // Scopes para filtros
    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', 'like', '%' . $brand . '%');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', 'like', '%' . $type . '%');
    }

    public function scopeByCapacityRange($query, $minCapacity = null, $maxCapacity = null)
    {
        if ($minCapacity !== null) {
            $query->where('capacity', '>=', $minCapacity);
        }
        if ($maxCapacity !== null) {
            $query->where('capacity', '<=', $maxCapacity);
        }
        return $query;
    }

    public function scopeByVoltageRange($query, $minVoltage = null, $maxVoltage = null)
    {
        if ($minVoltage !== null) {
            $query->where('voltage', '>=', $minVoltage);
        }
        if ($maxVoltage !== null) {
            $query->where('voltage', '<=', $maxVoltage);
        }
        return $query;
    }

    public function scopeByPriceRange($query, $minPrice = null, $maxPrice = null)
    {
        if ($minPrice !== null) {
            $query->where('price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('price', '<=', $maxPrice);
        }
        return $query;
    }
}
