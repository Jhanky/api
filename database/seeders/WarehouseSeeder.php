<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Warehouse;

class WarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'Bodega Central',
                'code' => 'BOD-001',
                'address' => 'Calle 123 # 45-67',
                'city_id' => null,
                'manager_name' => 'Juan Pérez',
                'phone' => '3001234567',
                'capacity' => 500.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bodega Norte',
                'code' => 'BOD-002',
                'address' => 'Carrera 45 # 12-34',
                'city_id' => null,
                'manager_name' => 'María González',
                'phone' => '3009876543',
                'capacity' => 300.00,
                'is_active' => true,
            ],
            [
                'name' => 'Bodega Sur',
                'code' => 'BOD-003',
                'address' => 'Avenida Principal # 78-90',
                'city_id' => null,
                'manager_name' => 'Carlos Rodríguez',
                'phone' => '3015556666',
                'capacity' => 400.00,
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $warehouse) {
            Warehouse::create($warehouse);
        }
    }
}
