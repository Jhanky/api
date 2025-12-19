<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectStateHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'from_state_id',
        'to_state_id',
        'reason',
        'notes',
        'changed_by',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function fromState(): BelongsTo
    {
        return $this->belongsTo(ProjectState::class, 'from_state_id');
    }

    public function toState(): BelongsTo
    {
        return $this->belongsTo(ProjectState::class, 'to_state_id');
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // Scopes
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('changed_at', '>=', now()->subDays($days));
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('changed_by', $userId);
    }

    public function scopeByStateChange($query, $fromStateId, $toStateId)
    {
        return $query->where('from_state_id', $fromStateId)
                    ->where('to_state_id', $toStateId);
    }

    // Helper methods
    public function getStateChangeDescription(): string
    {
        $from = $this->fromState?->name ?? 'Estado inicial';
        $to = $this->toState->name;

        return "De '{$from}' a '{$to}'";
    }

    public function wasChangedBy(User $user): bool
    {
        return $this->changed_by === $user->id;
    }

    public function getTimeSinceChange(): string
    {
        return $this->changed_at->diffForHumans();
    }
}
