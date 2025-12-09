<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProjectStatus;

class ProjectStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            [
                'name' => 'Activo',
                'description' => 'Proyecto en ejecución o activo',
                'color' => '#10B981', // Verde
                'is_active' => true
            ],
            [
                'name' => 'Desactivo',
                'description' => 'Proyecto pausado, cancelado o inactivo',
                'color' => '#EF4444', // Rojo
                'is_active' => true
            ]
        ];

        foreach ($statuses as $status) {
            ProjectStatus::create($status);
        }
    }
}
