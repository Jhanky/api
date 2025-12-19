<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ToolMaintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'tool_id',
        'maintenance_date',
        'description',
        'performed_by',
        'cost',
        'next_maintenance_date',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function tool(): BelongsTo
    {
        return $this->belongsTo(Tool::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTool($query, $toolId)
    {
        return $query->where('tool_id', $toolId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('next_maintenance_date', '<', now());
    }

    public function isOverdue(): bool
    {
        return $this->next_maintenance_date && $this->next_maintenance_date->isPast();
    }
}
