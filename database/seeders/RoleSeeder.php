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
                'name' => 'administrador',
                'display_name' => 'Administrador',
                'description' => 'Rol con acceso completo al sistema, puede gestionar usuarios, roles y configuraciones.',
                'is_active' => true,
            ],
            [
                'name' => 'gerente',
                'display_name' => 'Gerente',
                'description' => 'Rol gerencial con acceso a reportes, gestión de proyectos y supervisión de equipos.',
                'is_active' => true,
            ],
            [
                'name' => 'tecnico',
                'display_name' => 'Técnico',
                'description' => 'Rol para personal técnico, puede gestionar proyectos, instalaciones y mantenimientos.',
                'is_active' => true,
            ],
            [
                'name' => 'contador',
                'display_name' => 'Contador',
                'description' => 'Rol para personal contable, puede gestionar facturas, reportes financieros y contabilidad.',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                $role
            );
        }

        $this->command->info('Roles creados exitosamente: Administrador, Gerente, Técnico, Contador');
    }
}
