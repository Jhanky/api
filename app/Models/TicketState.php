<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketState extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'color',
        'is_final',
        'is_active',
    ];

    protected $casts = [
        'is_final' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFinal($query)
    {
        return $query->where('is_final', true);
    }

    public function scopeNonFinal($query)
    {
        return $query->where('is_final', false);
    }

    public function isFinalState(): bool
    {
        return $this->is_final;
    }
}
