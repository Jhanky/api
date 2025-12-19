<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClientContactPerson extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'name',
        'position',
        'email',
        'phone',
        'mobile',
        'whatsapp',
        'preferred_contact_method',
        'availability',
        'is_primary',
        'is_decision_maker',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'availability' => 'array',
        'is_primary' => 'boolean',
        'is_decision_maker' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the client that owns the contact person.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Scope to get only primary contacts.
     */
    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    /**
     * Scope to get only decision makers.
     */
    public function scopeDecisionMakers($query)
    {
        return $query->where('is_decision_maker', true);
    }

    /**
     * Scope to get only active contacts.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
