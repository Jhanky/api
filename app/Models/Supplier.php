<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Supplier extends Model
{
    use HasFactory;

    protected $primaryKey = 'supplier_id';

    protected $fillable = [
        'nit',
        'name',
        'email',
        'phone',
        'address',
        'city_id',
        'contact_person',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the city that the supplier belongs to.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the invoices for this supplier.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Get the payments for this supplier.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'supplier_id', 'supplier_id');
    }

    /**
     * Scope to get only active suppliers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the total amount of unpaid invoices.
     */
    public function getTotalUnpaidInvoicesAttribute(): float
    {
        return $this->invoices()
            ->where('status', '!=', 'pagada')
            ->sum('total_value');
    }
}
