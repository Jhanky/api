<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientInteractionAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_interaction_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'file_type',
        'uploaded_by',
    ];

    /**
     * Relaciones
     */
    public function interaction()
    {
        return $this->belongsTo(ClientInteraction::class, 'client_interaction_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Accesores
     */
    public function getFileSizeFormattedAttribute()
    {
        $size = $this->file_size;

        if ($size >= 1073741824) {
            return round($size / 1073741824, 2) . ' GB';
        } elseif ($size >= 1048576) {
            return round($size / 1048576, 2) . ' MB';
        } elseif ($size >= 1024) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return $size . ' bytes';
        }
    }

    public function getDownloadUrlAttribute()
    {
        return asset('storage/' . $this->file_path);
    }

    public function getFileTypeIconAttribute()
    {
        $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));

        $icons = [
            'pdf' => 'far fa-file-pdf text-red-500',
            'doc' => 'far fa-file-word text-blue-500',
            'docx' => 'far fa-file-word text-blue-500',
            'xls' => 'far fa-file-excel text-green-500',
            'xlsx' => 'far fa-file-excel text-green-500',
            'ppt' => 'far fa-file-powerpoint text-orange-500',
            'pptx' => 'far fa-file-powerpoint text-orange-500',
            'jpg' => 'far fa-file-image text-purple-500',
            'jpeg' => 'far fa-file-image text-purple-500',
            'png' => 'far fa-file-image text-purple-500',
            'gif' => 'far fa-file-image text-purple-500',
            'webp' => 'far fa-file-image text-purple-500',
            'zip' => 'far fa-file-archive text-yellow-500',
            'rar' => 'far fa-file-archive text-yellow-500',
            'txt' => 'far fa-file-alt text-gray-500',
        ];

        return $icons[$extension] ?? 'far fa-file text-gray-500';
    }

    public function getIsImageAttribute()
    {
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        return in_array($this->mime_type, $imageTypes);
    }

    public function getIsPdfAttribute()
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Scopes
     */
    public function scopeByFileType($query, $fileType)
    {
        return $query->where('file_type', $fileType);
    }
}
