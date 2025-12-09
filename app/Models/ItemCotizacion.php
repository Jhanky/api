<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemCotizacion extends Model
{
    use HasFactory;

    protected $table = 'quotation_items';
    protected $primaryKey = 'item_id';
    
    protected $fillable = [
        'quotation_id',
        'description',
        'item_type',
        'quantity',
        'unit',
        'unit_price',
        'partial_value',
        'profit_percentage',
        'profit',
        'total_value'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'partial_value' => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'profit' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    // Relaciones
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'quotation_id');
    }

    // Método para calcular valores automáticamente
    public function calculateValues()
    {
        $this->partial_value = $this->quantity * $this->unit_price;
        $this->profit = $this->partial_value * ($this->profit_percentage / 100);
        $this->total_value = $this->partial_value + $this->profit;
        $this->save();
        
        // Recalcular totales de la cotización
        $this->quotation->calculateTotals();
    }

    // Accessor para obtener información del item
    public function getItemInfoAttribute()
    {
        return [
            'description' => $this->description,
            'type' => $this->item_type,
            'quantity' => $this->quantity,
            'unit' => $this->unit,
            'unit_price' => $this->unit_price,
            'total' => $this->total_value
        ];
    }
}
