<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RequiredDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'flow_type',
        'state',
        'name',
        'description',
        'is_required',
        'display_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships
    public function projectDocuments(): HasMany
    {
        return $this->hasMany(ProjectDocument::class);
    }

    // Scopes
    public function scopeOperator($query)
    {
        return $query->where('flow_type', 'OPERATOR');
    }

    public function scopeUpme($query)
    {
        return $query->where('flow_type', 'UPME');
    }

    public function scopeByState($query, $state)
    {
        return $query->where('state', $state);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order');
    }
}
