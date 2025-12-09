<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    use HasFactory;

    protected $primaryKey = 'provider_id';

    protected $fillable = [
        'provider_name',
        'NIT'
    ];

    // Relación con facturas
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'provider_id', 'provider_id');
    }

    // Scopes para filtros
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('provider_name', 'like', '%' . $search . '%')
              ->orWhere('NIT', 'like', '%' . $search . '%');
        });
    }
}
