<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Milestone extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'milestone_type_id',
        'title',
        'description',
        'planned_date',
        'actual_date',
        'status',
        'amount_cop',
        'requires_verification',
        'verified_by',
        'verified_at',
        'verification_notes',
        'responsible_user_id',
        'notes',
    ];

    protected $casts = [
        'planned_date' => 'date',
        'actual_date' => 'date',
        'verified_at' => 'datetime',
        'amount_cop' => 'decimal:2',
        'requires_verification' => 'boolean',
    ];

    /**
     * Get the project that owns the milestone.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the milestone type.
     */
    public function milestoneType(): BelongsTo
    {
        return $this->belongsTo(MilestoneType::class);
    }

    /**
     * Get the user who verified the milestone.
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the responsible user for this milestone.
     */
    public function responsibleUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    /**
     * Scope to get only pending milestones.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only completed milestones.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get only overdue milestones.
     */
    public function scopeOverdue($query)
    {
        return $query->where('planned_date', '<', now())->where('status', '!=', 'completed');
    }

    /**
     * Scope to get only verified milestones.
     */
    public function scopeVerified($query)
    {
        return $query->whereNotNull('verified_at');
    }

    /**
     * Check if the milestone is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->planned_date && $this->planned_date->isPast() && $this->status !== 'completed';
    }

    /**
     * Check if the milestone is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the milestone is verified.
     */
    public function isVerified(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Get the completion percentage based on status.
     */
    public function getCompletionPercentageAttribute(): int
    {
        return match ($this->status) {
            'completed' => 100,
            'in_progress' => 50,
            'pending' => 0,
            'delayed' => 25,
            'cancelled' => 0,
            default => 0,
        };
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En Progreso',
            'completed' => 'Completado',
            'delayed' => 'Retrasado',
            'cancelled' => 'Cancelado',
            default => $this->status,
        };
    }

    /**
     * Get the days until planned date.
     */
    public function getDaysUntilPlannedAttribute(): int
    {
        return now()->diffInDays($this->planned_date, false);
    }
}
