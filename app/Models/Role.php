<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system_role',
        'is_active',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the users for the role.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    /**
     * Scope to get only active roles.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only system roles.
     */
    public function scopeSystemRoles($query)
    {
        return $query->where('is_system_role', true);
    }
}
