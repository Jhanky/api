<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quotation extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'client_id',
        'user_id',
        'status_id',
        'project_name',
        'system_type_id',  // FK a tabla system_types
        'grid_type_id',    // FK a tabla grid_types
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
        'issue_date',
        'expiration_date',
        'approved_at',
        'notes',
        'terms_conditions',
        'is_active',
    ];

    protected $casts = [
        'power_kwp' => 'decimal:2',
        'panel_count' => 'integer',
        'requires_financing' => 'boolean',
        'profit_percentage' => 'decimal:3',
        'iva_profit_percentage' => 'decimal:3',
        'commercial_management_percentage' => 'decimal:3',
        'administration_percentage' => 'decimal:3',
        'contingency_percentage' => 'decimal:3',
        'withholding_percentage' => 'decimal:3',
        'subtotal' => 'decimal:2',
        'profit' => 'decimal:2',
        'profit_iva' => 'decimal:2',
        'commercial_management' => 'decimal:2',
        'administration' => 'decimal:2',
        'contingency' => 'decimal:2',
        'withholdings' => 'decimal:2',
        'total_value' => 'decimal:2',
        'subtotal2' => 'decimal:2',
        'subtotal3' => 'decimal:2',
        'issue_date' => 'date',
        'expiration_date' => 'date',
        'approved_at' => 'date',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(QuotationStatus::class, 'status_id');
    }

    public function systemType(): BelongsTo
    {
        return $this->belongsTo(SystemType::class, 'system_type_id');
    }

    public function gridType(): BelongsTo
    {
        return $this->belongsTo(GridType::class, 'grid_type_id');
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function quotationProducts(): HasMany
    {
        return $this->hasMany(QuotationProduct::class);
    }

    public function quotationFollowUps(): HasMany
    {
        return $this->hasMany(QuotationFollowUp::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    // Alias para compatibilidad con el controlador
    public function usedProducts(): HasMany
    {
        return $this->quotationProducts();
    }

    public function items(): HasMany
    {
        return $this->quotationItems();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('status_id', $statusId);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<', now());
    }

    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expiration_date < now();
    }

    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    public function requiresFinancing(): bool
    {
        return $this->requires_financing;
    }

    public function getTotalValue(): float
    {
        // Calculate total from items and products
        $itemsTotal = $this->quotationItems->sum(function ($item) {
            return $item->quantity * $item->unit_price_cop;
        });

        $productsTotal = $this->quotationProducts->sum(function ($product) {
            return $product->quantity * $product->unit_price_cop;
        });

        return $itemsTotal + $productsTotal;
    }

    public function getTotalWithProfit(): float
    {
        $total = $this->getTotalValue();
        return $total * (1 + ($this->profit_percentage / 100));
    }
}
