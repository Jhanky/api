<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tool extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'tool_category_id',
        'tool_status_id',
        'serial_number',
        'model',
        'brand',
        'purchase_date',
        'warranty_expiry',
        'location',
        'specifications',
        'is_active',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'specifications' => 'array',
        'is_active' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ToolCategory::class, 'tool_category_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(ToolStatus::class, 'tool_status_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(ToolAssignment::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(ToolMaintenance::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('tool_category_id', $categoryId);
    }

    public function scopeByStatus($query, $statusId)
    {
        return $query->where('tool_status_id', $statusId);
    }

    public function isAvailable(): bool
    {
        return $this->assignments()->whereNull('returned_at')->doesntExist();
    }

    public function isUnderWarranty(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isFuture();
    }

    public function getCurrentAssignment()
    {
        return $this->assignments()->whereNull('returned_at')->first();
    }
}
