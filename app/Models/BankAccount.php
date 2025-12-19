<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    use HasFactory;

    protected $primaryKey = 'account_id';

    protected $fillable = [
        'account_number',
        'account_name',
        'bank_name',
        'account_type',
        'currency',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the payments associated with this bank account.
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class, 'bank_account_id', 'account_id');
    }

    /**
     * Scope to get only active accounts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the account type label.
     */
    public function getAccountTypeLabelAttribute(): string
    {
        return match ($this->account_type) {
            'ahorro' => 'Ahorros',
            'corriente' => 'Corriente',
            'nomina' => 'Nómina',
            default => $this->account_type,
        };
    }
}
