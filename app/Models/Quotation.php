<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    protected $primaryKey = 'quotation_id';
    
    protected $fillable = [
        'client_id',
        'user_id',
        'project_name',
        'system_type',
        'power_kwp',
        'panel_count',
        'requires_financing',
        'profit_percentage',
        'iva_profit_percentage',
        'commercial_management_percentage',
        'administration_percentage',
        'contingency_percentage',
        'withholding_percentage',
        'subtotal',
        'profit',
        'profit_iva',
        'commercial_management',
        'administration',
        'contingency',
        'withholdings',
        'total_value',
        'subtotal2',
        'subtotal3',
        'status_id'
    ];

    protected $casts = [
        'power_kwp' => 'decimal:2',
        'requires_financing' => 'boolean',
        'profit_percentage' => 'decimal:3',
        'iva_profit_percentage' => 'decimal:3',
        'commercial_management_percentage' => 'decimal:3',
        'administration_percentage' => 'decimal:3',
        'contingency_percentage' => 'decimal:3',
        'withholding_percentage' => 'decimal:3',
        'subtotal' => 'float',
        'profit' => 'float',
        'profit_iva' => 'float',
        'commercial_management' => 'float',
        'administration' => 'float',
        'contingency' => 'float',
        'withholdings' => 'float',
        'total_value' => 'float',
        'subtotal2' => 'float',
        'subtotal3' => 'float',
    ];

    // Relaciones
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(QuotationStatus::class, 'status_id', 'status_id');
    }

    public function usedProducts(): HasMany
    {
        return $this->hasMany(UsedProduct::class, 'quotation_id', 'quotation_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemCotizacion::class, 'quotation_id', 'quotation_id');
    }

    public function project(): HasMany
    {
        return $this->hasMany(Project::class, 'quotation_id', 'quotation_id');
    }

    // Métodos
    public function calculateTotals()
    {
        $subtotalProducts = $this->usedProducts()->sum('total_value');
        $subtotalItems = $this->items()->sum('total_value');
        
        $subtotal = $subtotalProducts + $subtotalItems;
        $commercialManagement = $subtotal * $this->commercial_management_percentage;
        $subtotal2 = $subtotal + $commercialManagement;
        
        $administrative = $subtotal2 * $this->administration_percentage;
        $contingency = $subtotal2 * $this->contingency_percentage;
        $profit = $subtotal2 * $this->profit_percentage;
        $ivaProfit = $profit * $this->iva_profit_percentage;
        
        $subtotal3 = $subtotal2 + $administrative + $contingency + $profit + $ivaProfit;
        $withholdings = $subtotal3 * $this->withholding_percentage;
        $totalValue = $subtotal3 + $withholdings;
        
        $this->update([
            'subtotal' => $subtotal,
            'subtotal2' => $subtotal2,
            'subtotal3' => $subtotal3,
            'profit' => $profit,
            'profit_iva' => $ivaProfit,
            'commercial_management' => $commercialManagement,
            'administration' => $administrative,
            'contingency' => $contingency,
            'withholdings' => $withholdings,
            'total_value' => $totalValue
        ]);
    }

    // Accessor para número de cotización
    public function getQuotationNumberAttribute()
    {
        return 'COT-' . str_pad($this->quotation_id, 6, '0', STR_PAD_LEFT);
    }
}
