<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectState extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'slug',
        'description',
        'color',
        'icon',
        'phase',
        'display_order',
        'estimated_duration',
        'is_final',
        'requires_approval',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_final' => 'boolean',
        'requires_approval' => 'boolean',
    ];

    // Relationships
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'current_state_id');
    }

    public function projectStateHistoryFrom(): HasMany
    {
        return $this->hasMany(ProjectStateHistory::class, 'from_state_id');
    }

    public function projectStateHistoryTo(): HasMany
    {
        return $this->hasMany(ProjectStateHistory::class, 'to_state_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('id');
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Obtener el slug (usa code o genera desde name)
     */
    public function getSlugAttribute(): string
    {
        return $this->code ?? strtolower(str_replace(' ', '_', $this->name));
    }
}
