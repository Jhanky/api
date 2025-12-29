<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'client_id',
        'quotation_id',
        'current_state_id',
        'name',
        'description',
        'installation_address',
        'coordinates',
        'start_date',
        'estimated_end_date',
        'actual_end_date',
        'contracted_value_cop',
        'total_cost_cop',
        'project_manager_id',
        'technical_leader_id',
        'priority',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'estimated_end_date' => 'date',
        'actual_end_date' => 'date',
        'contracted_value_cop' => 'decimal:2',
        'total_cost_cop' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function currentState(): BelongsTo
    {
        return $this->belongsTo(ProjectState::class, 'current_state_id');
    }

    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    public function technicalLeader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technical_leader_id');
    }

    public function projectStateHistory(): HasMany
    {
        return $this->hasMany(ProjectStateHistory::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(ProjectNote::class);
    }

    public function technicalSpecs(): HasOne
    {
        return $this->hasOne(ProjectTechnicalSpecs::class);
    }

    public function projectDocuments(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    // Nota: Relaciones projectComments, projectMaterialAssignments y toolAssignments removidas
    // porque los modelos correspondientes no existen en el sistema actual.

    public function costCenter(): HasOne
    {
        return $this->hasOne(CostCenter::class);
    }

    public function upmeDetail(): HasOne
    {
        return $this->hasOne(ProjectUpmeDetail::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByState($query, $stateId)
    {
        return $query->where('current_state_id', $stateId);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeOverdue($query)
    {
        return $query->where('estimated_end_date', '<', now())
                    ->whereNull('actual_end_date');
    }

    public function scopeCompleted($query)
    {
        return $query->whereNotNull('actual_end_date');
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isCompleted(): bool
    {
        return !is_null($this->actual_end_date);
    }

    public function isOverdue(): bool
    {
        return !$this->isCompleted() && $this->estimated_end_date < now();
    }

    public function getProgressPercentage(): float
    {
        // Calculate progress based on current state phase
        $phaseOrder = [
            'commercial' => 1,
            'legal' => 2,
            'technical' => 3,
            'financial' => 4,
            'completed' => 5,
        ];

        $currentPhase = $this->currentState?->phase ?? 'commercial';
        $currentOrder = $phaseOrder[$currentPhase] ?? 1;

        return min(100, ($currentOrder / 5) * 100);
    }

    public function getTotalCost(): float
    {
        return $this->total_cost_cop ?? $this->contracted_value_cop;
    }

    public function getProfitMargin(): float
    {
        if (!$this->total_cost_cop || !$this->contracted_value_cop) {
            return 0;
        }

        return (($this->contracted_value_cop - $this->total_cost_cop) / $this->contracted_value_cop) * 100;
    }

    public function changeState(ProjectState $newState, User $user, ?string $reason = null, ?string $notes = null): void
    {
        $oldState = $this->currentState;

        $this->update(['current_state_id' => $newState->id]);

        // Create history record
        $this->projectStateHistory()->create([
            'from_state_id' => $oldState?->id,
            'to_state_id' => $newState->id,
            'reason' => $reason,
            'notes' => $notes,
            'changed_by' => $user->id,
            'changed_at' => now(),
        ]);
    }
}
