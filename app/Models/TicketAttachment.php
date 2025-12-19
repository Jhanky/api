<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'uploaded_by',
        'description',
        'is_active',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_active' => 'boolean',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
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
        $bytes = $this->file_size;

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

    public function getFileExtension(): string
    {
        return strtolower(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    public function isImage(): bool
    {
        return in_array($this->getFileExtension(), ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp']);
    }
}
