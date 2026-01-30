<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\Department;
use Illuminate\Database\Seeder;

class GeographySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['id' => 1, 'name' => 'Cundinamarca'],
            ['id' => 2, 'name' => 'Antioquia'],
            ['id' => 3, 'name' => 'Valle del Cauca'],
            ['id' => 4, 'name' => 'Atlántico'],
            ['id' => 5, 'name' => 'Santander'],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(['id' => $dept['id']], $dept);
        }

        $cities = [
            ['id' => 1, 'department_id' => 1, 'name' => 'Bogotá'],
            ['id' => 2, 'department_id' => 2, 'name' => 'Medellín'],
            ['id' => 3, 'department_id' => 3, 'name' => 'Cali'],
            ['id' => 4, 'department_id' => 3, 'name' => 'Yumbo'],
            ['id' => 5, 'department_id' => 2, 'name' => 'Itagüí'],
            ['id' => 6, 'department_id' => 4, 'name' => 'Barranquilla'],
            ['id' => 7, 'department_id' => 5, 'name' => 'Bucaramanga'],
        ];

        foreach ($cities as $city) {
            City::updateOrCreate(['id' => $city['id']], $city);
        }
    }
}
