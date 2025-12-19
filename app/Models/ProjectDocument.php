<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'project_id',
        'document_type_id',
        'milestone_id',
        'name',
        'original_filename',
        'file_path',
        'mime_type',
        'file_size',
        'file_extension',
        'description',
        'document_date',
        'responsible',
        'version',
        'replaces_document_id',
        'is_public',
        'requires_approval',
        'approved_by',
        'approved_at',
        'uploaded_by',
        'is_active',
    ];

    protected $casts = [
        'document_date' => 'date',
        'approved_at' => 'datetime',
        'is_public' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'file_size' => 'integer',
    ];

    /**
     * Get the project that owns the document.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the document type.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Get the milestone associated with this document.
     */
    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    /**
     * Get the document that this replaces.
     */
    public function replacesDocument(): BelongsTo
    {
        return $this->belongsTo(ProjectDocument::class, 'replaces_document_id');
    }

    /**
     * Get the documents that replace this one.
     */
    public function replacedBy()
    {
        return $this->hasMany(ProjectDocument::class, 'replaces_document_id');
    }

    /**
     * Get the user who approved the document.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the user who uploaded the document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope to get only active documents.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only public documents.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get only approved documents.
     */
    public function scopeApproved($query)
    {
        return $query->whereNotNull('approved_at');
    }

    /**
     * Check if the document is approved.
     */
    public function isApproved(): bool
    {
        return !is_null($this->approved_at);
    }

    /**
     * Get the file size in human readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the full file path.
     */
    public function getFullPathAttribute(): string
    {
        return storage_path('app/' . $this->file_path);
    }
}
