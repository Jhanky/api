<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIQuotationItem extends Model
{
    use HasFactory;

    protected $table = 'ai_quotation_items';
    protected $primaryKey = 'item_id';

    protected $fillable = [
        'ai_quotation_id',
        'description',
        'item_type',
        'quantity',
        'unit',
        'unit_price',
        'profit_percentage',
        'total_price',
        'notes'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'profit_percentage' => 'decimal:2',
        'total_price' => 'decimal:2'
    ];

    public function quotation()
    {
        return $this->belongsTo(AIQuotation::class, 'ai_quotation_id', 'ai_quotation_id');
    }

    public function scopeByItemType($query, $itemType)
    {
        return $query->where('item_type', $itemType);
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('unit_price', [$minPrice, $maxPrice]);
    }
}
