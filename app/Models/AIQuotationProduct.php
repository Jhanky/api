<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIQuotationProduct extends Model
{
    use HasFactory;

    protected $table = 'ai_quotation_products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'ai_quotation_id',
        'product_type',
        'catalog_product_id',
        'quantity',
        'unit_price',
        'total_price',
        'brand',
        'model',
        'specifications'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function quotation()
    {
        return $this->belongsTo(AIQuotation::class, 'ai_quotation_id', 'ai_quotation_id');
    }

    public function catalogProduct()
    {
        // Relación polimórfica con el catálogo principal
        switch ($this->product_type) {
            case 'panel':
                return $this->belongsTo(Panel::class, 'catalog_product_id', 'panel_id');
            case 'inverter':
                return $this->belongsTo(Inverter::class, 'catalog_product_id', 'inverter_id');
            case 'battery':
                return $this->belongsTo(Battery::class, 'catalog_product_id', 'battery_id');
            default:
                return null;
        }
    }
}
