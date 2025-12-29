<?php

namespace Database\Seeders;

use App\Models\ClientType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clientTypes = [
            [
                'id' => 1,
                'name' => 'Residencial',
                'slug' => 'residencial',
                'description' => 'Clientes residenciales para instalaciones solares en viviendas',
                'is_active' => true,
            ],
            [
                'id' => 2,
                'name' => 'Comercial',
                'slug' => 'comercial',
                'description' => 'Clientes comerciales para instalaciones solares en empresas',
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'Industrial',
                'slug' => 'industrial',
                'description' => 'Clientes industriales para grandes instalaciones solares',
                'is_active' => true,
            ],
            [
                'id' => 4,
                'name' => 'Institucional',
                'slug' => 'institucional',
                'description' => 'Clientes institucionales como escuelas, hospitales, gobiernos',
                'is_active' => true,
            ],
        ];

        foreach ($clientTypes as $type) {
            ClientType::create($type);
        }
    }
}
