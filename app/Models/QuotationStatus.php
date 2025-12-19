<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuotationStatus extends Model
{
    use HasFactory;

    protected $primaryKey = 'id'; // Laravel usa 'id' por defecto, pero aseguramos consistencia

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'display_order',
        'is_final',
        'is_active',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships
    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'status_id');
    }

    public function quotationFollowUpsFrom(): HasMany
    {
        return $this->hasMany(QuotationFollowUp::class, 'from_status_id');
    }

    public function quotationFollowUpsTo(): HasMany
    {
        return $this->hasMany(QuotationFollowUp::class, 'to_status_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }

    // Helper methods
    public function isFinal(): bool
    {
        return $this->is_final;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
