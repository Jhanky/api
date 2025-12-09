<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $primaryKey = 'location_id';
    
    protected $fillable = [
        'department',
        'municipality',
        'radiation'
    ];

    protected $casts = [
        'radiation' => 'float',
    ];

    // Scopes para filtros
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', 'like', '%' . $department . '%');
    }

    public function scopeByMunicipality($query, $municipality)
    {
        return $query->where('municipality', 'like', '%' . $municipality . '%');
    }

    public function scopeByRadiationRange($query, $minRadiation = null, $maxRadiation = null)
    {
        if ($minRadiation !== null) {
            $query->where('radiation', '>=', $minRadiation);
        }
        if ($maxRadiation !== null) {
            $query->where('radiation', '<=', $maxRadiation);
        }
        return $query;
    }

    public function scopeHighRadiation($query, $threshold = 1480.0)
    {
        return $query->where('radiation', '>=', $threshold);
    }

    public function scopeLowRadiation($query, $threshold = 1420.0)
    {
        return $query->where('radiation', '<=', $threshold);
    }

    // Accessor para formatear la radiación
    public function getFormattedRadiationAttribute()
    {
        return number_format($this->radiation, 2) . ' W/m²';
    }

    // Método para obtener el nombre completo de la ubicación
    public function getFullLocationAttribute()
    {
        return $this->municipality . ', ' . $this->department;
    }

    // Método para clasificar el nivel de radiación
    public function getRadiationLevelAttribute()
    {
        if ($this->radiation >= 1480) {
            return 'Excelente';
        } elseif ($this->radiation >= 1450) {
            return 'Muy Buena';
        } elseif ($this->radiation >= 1420) {
            return 'Buena';
        } elseif ($this->radiation >= 1400) {
            return 'Regular';
        } else {
            return 'Baja';
        }
    }

    // Relaciones
    public function projects()
    {
        return $this->hasMany(Project::class, 'location_id', 'location_id');
    }
}
