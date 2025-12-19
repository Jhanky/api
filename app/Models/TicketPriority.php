<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketPriority extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'level',
        'color',
        'is_active',
    ];

    protected $casts = [
        'level' => 'integer',
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

    public function scopeByLevel($query, $direction = 'asc')
    {
        return $query->orderBy('level', $direction);
    }

    public function isHighPriority(): bool
    {
        return $this->level >= 3; // Asumiendo que 3+ es alta prioridad
    }
}
