<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    // ========================================
    // RELACIONES
    // ========================================
    
    /**
     * Obtener las facturas que usan este método de pago
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'payment_method_id');
    }

    // ========================================
    // SCOPES
    // ========================================
    
    /**
     * Scope para métodos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por código
     */
    public function scopeByCode($query, $code)
    {
        return $query->where('code', $code);
    }

    // ========================================
    // MÉTODOS ESTÁTICOS
    // ========================================
    
    /**
     * Obtener método por código
     */
    public static function getByCode($code)
    {
        return static::where('code', $code)->first();
    }

    /**
     * Obtener todos los métodos activos
     */
    public static function getActiveMethods()
    {
        return static::active()->get();
    }

    /**
     * Obtener opciones para formularios
     */
    public static function getOptions()
    {
        return static::active()
            ->orderBy('name')
            ->pluck('name', 'id');
    }

    /**
     * Obtener opciones con códigos
     */
    public static function getOptionsWithCodes()
    {
        return static::active()
            ->orderBy('name')
            ->get()
            ->mapWithKeys(function ($method) {
                return [$method->id => "{$method->name} ({$method->code})"];
            });
    }
}
