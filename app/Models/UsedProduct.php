<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class UsedProduct extends Model
{
    use HasFactory;

    protected $primaryKey = 'used_product_id';
    
    protected $fillable = [
        'quotation_id',
        'product_type',
        'product_id',
        'quantity',
        'unit_price',
        'partial_value',
        'profit_percentage',
        'profit',
        'total_value'
    ];

    protected $casts = [
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

    // Relación polimórfica para obtener el producto específico
    public function product()
    {
        switch ($this->product_type) {
            case 'panel':
                return $this->belongsTo(Panel::class, 'product_id', 'panel_id');
            case 'inverter':
                return $this->belongsTo(Inverter::class, 'product_id', 'inverter_id');
            case 'battery':
                return $this->belongsTo(Battery::class, 'product_id', 'battery_id');
            default:
                return null;
        }
    }

    // Relaciones específicas para cada tipo de producto
    public function panel()
    {
        return $this->belongsTo(Panel::class, 'product_id', 'panel_id');
    }

    public function inverter()
    {
        return $this->belongsTo(Inverter::class, 'product_id', 'inverter_id');
    }

    public function battery()
    {
        return $this->belongsTo(Battery::class, 'product_id', 'battery_id');
    }

    // Método para obtener el producto según el tipo
    public function getProductAttribute()
    {
        switch ($this->product_type) {
            case 'panel':
                return $this->panel;
            case 'inverter':
                return $this->inverter;
            case 'battery':
                return $this->battery;
            default:
                return null;
        }
    }

    // Método para obtener el producto relacionado (para eager loading)
    public function getRelatedProduct()
    {
        switch ($this->product_type) {
            case 'panel':
                return $this->panel;
            case 'inverter':
                return $this->inverter;
            case 'battery':
                return $this->battery;
            default:
                return null;
        }
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

    // Accessor para obtener información del producto
    public function getProductInfoAttribute()
    {
        $product = $this->product();
        if ($product) {
            return $product->first();
        }
        return null;
    }
}
