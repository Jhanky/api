<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Client extends Model
{
    use HasFactory;

    protected $primaryKey = 'client_id';

    protected $fillable = [
        'nic',
        'client_type',
        'name',
        'department',
        'city',
        'address',
        'monthly_consumption_kwh',
        'energy_rate',
        'network_type',
        'user_id',
        'is_active'
    ];

    protected $casts = [
        'monthly_consumption_kwh' => 'float',
        'energy_rate' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación con el usuario
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para clientes activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para filtrar por tipo de cliente
     */
    public function scopeByType($query, $type)
    {
        return $query->where('client_type', $type);
    }

    /**
     * Scope para filtrar por departamento
     */
    public function scopeByDepartment($query, $department)
    {
        return $query->where('department', $department);
    }

    /**
     * Scope para filtrar por ciudad
     */
    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    /**
     * Calcular consumo mensual estimado
     */
    public function getMonthlyEstimatedCostAttribute()
    {
        return $this->monthly_consumption_kwh * $this->energy_rate;
    }

    /**
     * Obtener el nombre completo con NIC
     */
    public function getFullIdentificationAttribute()
    {
        return $this->name . ' (' . $this->nic . ')';
    }
}
