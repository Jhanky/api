<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the clients for the client type.
     */
    public function clients(): HasMany
    {
        return $this->hasMany(Client::class, 'client_type_id');
    }

    /**
     * Scope to get only active client types.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
