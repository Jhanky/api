<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectUpmeDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'radicado_number',
        'case_number',
        'status',
        'filing_date',
        'filing_comments',
        'consultation_url',
        'response_date',
        'response_number',
        'response_comments',
    ];

    protected $casts = [
        'filing_date' => 'date',
        'response_date' => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
