<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CostCenterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $costCenters = [
            [
                'cost_center_name' => 'Marketing'
            ],
            [
                'cost_center_name' => 'IT'
            ],
            [
                'cost_center_name' => 'Proyectos Especiales'
            ],
            [
                'cost_center_name' => 'Administración'
            ],
            [
                'cost_center_name' => 'Ventas'
            ],
            [
                'cost_center_name' => 'Instalaciones'
            ],
            [
                'cost_center_name' => 'Mantenimiento'
            ],
            [
                'cost_center_name' => 'Investigación y Desarrollo'
            ],
            [
                'cost_center_name' => 'Recursos Humanos'
            ],
            [
                'cost_center_name' => 'Finanzas'
            ]
        ];

        foreach ($costCenters as $costCenter) {
            \App\Models\CostCenter::create($costCenter);
        }
    }
}
