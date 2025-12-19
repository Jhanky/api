<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MaintenanceType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'frequency_days',
        'is_active',
    ];

    protected $casts = [
        'frequency_days' => 'integer',
        'is_active' => 'boolean',
    ];

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFrequencyDescription(): string
    {
        if (!$this->frequency_days) {
            return 'Sin frecuencia definida';
        }

        if ($this->frequency_days < 30) {
            return "Cada {$this->frequency_days} días";
        }

        $months = round($this->frequency_days / 30);
        return $months === 1 ? 'Mensual' : "Cada {$months} meses";
    }
}
