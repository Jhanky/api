<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceEvidence extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_id',
        'file_path',
        'file_name',
        'file_type',
        'uploaded_by',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function maintenance(): BelongsTo
    {
        return $this->belongsTo(Maintenance::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getFileUrl(): string
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = filesize(storage_path('app/public/' . $this->file_path));

        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
