<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'slug' => 'admin',
                'description' => 'Acceso total al sistema',
                'is_system_role' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Gerente Comercial',
                'slug' => 'sales-manager',
                'description' => 'Gestión de clientes, cotizaciones y proyectos comerciales',
                'is_system_role' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Gerente de Proyectos',
                'slug' => 'project-manager',
                'description' => 'Gestión completa de proyectos y seguimiento',
                'is_system_role' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Técnico',
                'slug' => 'technician',
                'description' => 'Ejecución de instalaciones y mantenimientos',
                'is_system_role' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Contador',
                'slug' => 'accountant',
                'description' => 'Gestión financiera y contable',
                'is_system_role' => true,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
