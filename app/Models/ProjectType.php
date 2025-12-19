<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProjectType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'project_type_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Helper methods
    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isResidential(): bool
    {
        return $this->slug === 'residencial';
    }

    public function isCommercial(): bool
    {
        return $this->slug === 'comercial';
    }

    public function isIndustrial(): bool
    {
        return $this->slug === 'industrial';
    }

    public function isAgroindustrial(): bool
    {
        return $this->slug === 'agroindustrial';
    }

    public function isSolarPump(): bool
    {
        return $this->slug === 'bombeo-solar';
    }
}
