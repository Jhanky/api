<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'ticket_type_id',
        'ticket_priority_id',
        'ticket_state_id',
        'created_by',
        'assigned_to',
        'project_id',
        'client_id',
        'due_date',
        'resolved_at',
        'is_active',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'resolved_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function ticketPriority(): BelongsTo
    {
        return $this->belongsTo(TicketPriority::class);
    }

    public function ticketState(): BelongsTo
    {
        return $this->belongsTo(TicketState::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByStatus($query, $stateId)
    {
        return $query->where('ticket_state_id', $stateId);
    }

    public function scopeByPriority($query, $priorityId)
    {
        return $query->where('ticket_priority_id', $priorityId);
    }

    public function scopeByType($query, $typeId)
    {
        return $query->where('ticket_type_id', $typeId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNull('resolved_at');
    }

    public function scopeResolved($query)
    {
        return $query->whereNotNull('resolved_at');
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->resolved_at;
    }

    public function isResolved(): bool
    {
        return !is_null($this->resolved_at);
    }

    public function isClosed(): bool
    {
        return $this->ticketState && $this->ticketState->is_final;
    }

    public function getDaysOpen(): int
    {
        $endDate = $this->resolved_at ?? now();
        return $this->created_at->diffInDays($endDate);
    }
}
