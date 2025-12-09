<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $primaryKey = 'project_id';

    protected $fillable = [
        'quotation_id',
        'client_id',
        'location_id',
        'status_id',
        'project_name',
        'start_date',
        'estimated_end_date',
        'actual_end_date',
        'project_manager_id',
        'budget',
        'notes',
        'latitude',
        'longitude',
        'installation_address',
        'cover_image',
        'cover_image_alt'
    ];

    protected $casts = [
        'start_date' => 'date',
        'estimated_end_date' => 'date',
        'actual_end_date' => 'date',
        'budget' => 'decimal:2',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8'
    ];

    // Relaciones
    public function quotation()
    {
        return $this->belongsTo(Quotation::class, 'quotation_id', 'quotation_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id', 'location_id');
    }

    public function status()
    {
        return $this->belongsTo(ProjectStatus::class, 'status_id', 'status_id');
    }

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id', 'id');
    }

    // Scopes útiles
    public function scopeActive($query)
    {
        return $query->whereHas('status', function ($q) {
            $q->where('is_active', true);
        });
    }

    public function scopeByStatus($query, $statusName)
    {
        return $query->whereHas('status', function ($q) use ($statusName) {
            $q->where('name', $statusName);
        });
    }
}