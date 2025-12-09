<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCenter extends Model
{
    use HasFactory;

    protected $primaryKey = 'cost_center_id';

    protected $fillable = [
        'cost_center_name'
    ];

    // Relación con facturas
    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'cost_center_id', 'cost_center_id');
    }

    // Scopes para filtros
    public function scopeSearch($query, $search)
    {
        return $query->where('cost_center_name', 'like', '%' . $search . '%');
    }
}
