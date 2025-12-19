<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Remission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'from_warehouse_id',
        'to_warehouse_id',
        'to_project_id',
        'remission_type',
        'status',
        'issue_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'carrier_name',
        'tracking_number',
        'vehicle_plate',
        'driver_name',
        'driver_phone',
        'delivery_address',
        'delivery_contact_name',
        'delivery_contact_phone',
        'notes',
        'prepared_by',
        'approved_by',
        'received_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
    ];

    /**
     * Get the warehouse from which items are being sent.
     */
    public function fromWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'from_warehouse_id');
    }

    /**
     * Get the warehouse to which items are being sent.
     */
    public function toWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'to_warehouse_id');
    }

    /**
     * Get the project to which items are being dispatched.
     */
    public function toProject(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'to_project_id');
    }

    /**
     * Get the user who prepared the remission.
     */
    public function preparer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }

    /**
     * Get the user who approved the remission.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who received the remission.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /**
     * Get the remission items.
     */
    public function items(): HasMany
    {
        return $this->hasMany(RemissionItem::class);
    }

    /**
     * Scope to get only draft remissions.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get only pending remissions.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get only received remissions.
     */
    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    /**
     * Scope to get only cancelled remissions.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Check if the remission is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->expected_delivery_date &&
               $this->expected_delivery_date->isPast() &&
               in_array($this->status, ['pending', 'in_transit']);
    }

    /**
     * Get the total quantity of items in the remission.
     */
    public function getTotalQuantityAttribute(): float
    {
        return $this->items()->sum('quantity_requested');
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Borrador',
            'pending' => 'Pendiente',
            'in_transit' => 'En Tránsito',
            'received' => 'Recibida',
            'cancelled' => 'Cancelada',
            default => $this->status,
        };
    }

    /**
     * Get the remission type label.
     */
    public function getRemissionTypeLabelAttribute(): string
    {
        return match ($this->remission_type) {
            'transfer' => 'Transferencia',
            'dispatch' => 'Despacho',
            'loan' => 'Préstamo',
            'return' => 'Devolución',
            default => $this->remission_type,
        };
    }
}
