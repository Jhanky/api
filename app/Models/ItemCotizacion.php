<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCotizacion extends Model
{
    use HasFactory;

    protected $table = 'items_cotizacion';
    protected $primaryKey = 'id_item';
    
    protected $fillable = [
        'id_cotizacion',
        'descripcion',
        'tipo_item',
        'cantidad',
        'unidad',
        'precio_unitario',
        'valor_parcial',
        'porcentaje_ganancia',
        'ganancia',
        'valor_total_item'
    ];

    protected $casts = [
        'cantidad' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
        'valor_parcial' => 'decimal:2',
        'porcentaje_ganancia' => 'decimal:2',
        'ganancia' => 'decimal:2',
        'valor_total_item' => 'decimal:2',
    ];

    // Relaciones
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'id_cotizacion', 'quotation_id');
    }

    // Método para calcular valores automáticamente
    public function calculateValues()
    {
        $this->valor_parcial = $this->cantidad * $this->precio_unitario;
        $this->ganancia = $this->valor_parcial * ($this->porcentaje_ganancia / 100);
        $this->valor_total_item = $this->valor_parcial + $this->ganancia;
        $this->save();
        
        // Recalcular totales de la cotización
        $this->quotation->calculateTotals();
    }

    // Scopes
    public function scopeByType($query, $type)
    {
        return $query->where('tipo_item', $type);
    }
}
