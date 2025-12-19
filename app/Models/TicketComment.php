<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment',
        'is_internal',
        'is_active',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function isInternal(): bool
    {
        return $this->is_internal;
    }
}
