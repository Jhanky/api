<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationFollowUp extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'from_status_id',
        'to_status_id',
        'action_type',
        'notes',
        'client_comments',
        'changes',
        'next_action_date',
        'next_action_description',
        'next_action_completed',
        'user_id',
        'action_date',
    ];

    protected $casts = [
        'changes' => 'array',
        'next_action_date' => 'datetime',
        'next_action_completed' => 'boolean',
        'action_date' => 'datetime',
    ];

    // Relationships
    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(QuotationStatus::class, 'from_status_id');
    }

    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(QuotationStatus::class, 'to_status_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeByActionType($query, $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopePendingActions($query)
    {
        return $query->where('next_action_completed', false)
                    ->whereNotNull('next_action_date');
    }

    public function scopeCompletedActions($query)
    {
        return $query->where('next_action_completed', true);
    }

    public function scopeOverdueActions($query)
    {
        return $query->where('next_action_completed', false)
                    ->where('next_action_date', '<', now());
    }

    // Helper methods
    public function isStatusChange(): bool
    {
        return $this->action_type === 'status_change';
    }

    public function isClientFeedback(): bool
    {
        return $this->action_type === 'client_feedback';
    }

    public function isRevisionRequested(): bool
    {
        return $this->action_type === 'revision_requested';
    }

    public function isPriceNegotiation(): bool
    {
        return $this->action_type === 'price_negotiation';
    }

    public function isApproval(): bool
    {
        return $this->action_type === 'approval';
    }

    public function isRejection(): bool
    {
        return $this->action_type === 'rejection';
    }

    public function isExpirationReminder(): bool
    {
        return $this->action_type === 'expiration_reminder';
    }

    public function hasNextAction(): bool
    {
        return !is_null($this->next_action_date) && !$this->next_action_completed;
    }

    public function isNextActionOverdue(): bool
    {
        return $this->hasNextAction() && $this->next_action_date < now();
    }

    public function completeNextAction(): void
    {
        $this->update([
            'next_action_completed' => true,
        ]);
    }
}
