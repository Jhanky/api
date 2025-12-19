<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_id',
        'user_id',
        'assigned_by',
        'assigned_at',
        'returned_at',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'returned_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeCurrent($query)
    {
        return $query->whereNull('returned_at');
    }

    public function scopeReturned($query)
    {
        return $query->whereNotNull('returned_at');
    }

    public function isReturned(): bool
    {
        return !is_null($this->returned_at);
    }

    public function getDuration()
    {
        if (!$this->returned_at) {
            return null;
        }

        return $this->assigned_at->diff($this->returned_at);
    }
}
