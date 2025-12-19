<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FileAuditLog extends Model
{
    use HasFactory;

    protected $primaryKey = 'log_id';

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'action',
        'file_path',
        'file_name',
        'file_size',
        'user_id',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user who performed the action.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get logs by entity type.
     */
    public function scopeByEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    /**
     * Scope to get logs by action.
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to get logs by user.
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get logs within date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get the action label.
     */
    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'upload' => 'Subida',
            'delete' => 'Eliminación',
            'replace' => 'Reemplazo',
            'download' => 'Descarga',
            default => $this->action,
        };
    }

    /**
     * Get the entity type label.
     */
    public function getEntityTypeLabelAttribute(): string
    {
        return match ($this->entity_type) {
            'invoice' => 'Factura',
            'payment' => 'Pago',
            'document' => 'Documento',
            default => $this->entity_type,
        };
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        if (!$this->file_size) {
            return 'N/A';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}
