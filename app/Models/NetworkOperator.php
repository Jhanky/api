<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NetworkOperator extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'contact_info',
        'is_active',
    ];

    protected $casts = [
        'contact_info' => 'array',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
