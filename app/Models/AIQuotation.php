<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AIQuotation extends Model
{
    use HasFactory;

    protected $table = 'ai_quotations';
    protected $primaryKey = 'ai_quotation_id';

    protected $fillable = [
        'client_name',
        'project_name',
        'system_type',
        'power_kwp',
        'panel_count',
        'total_value'
    ];

    protected $casts = [
        'power_kwp' => 'decimal:2',
        'panel_count' => 'integer',
        'total_value' => 'decimal:2'
    ];

    public function products()
    {
        return $this->hasMany(AIQuotationProduct::class, 'ai_quotation_id', 'ai_quotation_id');
    }

    public function items()
    {
        return $this->hasMany(AIQuotationItem::class, 'ai_quotation_id', 'ai_quotation_id');
    }

    public function scopeBySystemType($query, $systemType)
    {
        return $query->where('system_type', $systemType);
    }
}
