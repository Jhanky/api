<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'maintenance_type_id',
        'scheduled_date',
        'completed_date',
        'description',
        'assigned_to',
        'performed_by',
        'cost',
        'status',
        'priority',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function maintenanceType(): BelongsTo
    {
        return $this->belongsTo(MaintenanceType::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function evidences(): HasMany
    {
        return $this->hasMany(MaintenanceEvidence::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_date', '<', now())
                    ->whereNull('completed_date');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function isOverdue(): bool
    {
        return $this->scheduled_date && $this->scheduled_date->isPast() && !$this->completed_date;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function getDuration()
    {
        if (!$this->completed_date) {
            return null;
        }

        return $this->scheduled_date->diff($this->completed_date);
    }
}
