<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull as Middleware;

class ConvertEmptyStringsToNull extends Middleware
{
    /**
     * The names of the attributes that should not be converted to null.
     *
     * @var array<int, string>
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
        // Campos de proyectos que no deben convertirse a null
        'id',
        'nombre_proyecto',
        'codigo_proyecto',
        'project_id',
        'project_name',
        'quotation_id',
        'client_id',
        'location_id',
        'status_id',
        'project_manager_id',
        'budget',
        'notes',
        'latitude',
        'longitude',
        'installation_address'
    ];
}
