<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cost_centers';
    protected $primaryKey = 'cost_center_id';

    protected $fillable = [
        'code',
        'name',
        'description',
        'department_id',
        'project_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the department associated with this cost center.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the project associated with this cost center.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the invoices for this cost center.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class, 'cost_center_id', 'cost_center_id');
    }

    /**
     * Scope to search cost centers by name or code.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('code', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to get only active cost centers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get cost centers associated with a project.
     */
    public function scopeWithProject($query)
    {
        return $query->whereNotNull('project_id');
    }

    /**
     * Scope to get cost centers not associated with any project.
     */
    public function scopeWithoutProject($query)
    {
        return $query->whereNull('project_id');
    }
}
