<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        User::create([
            'name' => 'Administrador',
            'username' => 'admin',
            'email' => 'admin@energy4cero.com',
            'password' => Hash::make('password123'),
            'phone' => '+573001234567',
            'job_title' => 'Administrador del Sistema',
            'profile_photo' => null,
        ]);

        // Crear usuario de prueba
        User::create([
            'name' => 'Juan Pérez',
            'username' => 'juan.perez',
            'email' => 'juan.perez@energy4cero.com',
            'password' => Hash::make('password123'),
            'phone' => '+573001234568',
            'job_title' => 'Ingeniero de Ventas',
            'profile_photo' => null,
        ]);

        // Crear otro usuario de prueba
        User::create([
            'name' => 'María García',
            'username' => 'maria.garcia',
            'email' => 'maria.garcia@energy4cero.com',
            'password' => Hash::make('password123'),
            'phone' => '+573001234569',
            'job_title' => 'Técnico de Instalación',
            'profile_photo' => null,
        ]);

        $this->command->info('Usuarios de prueba creados exitosamente con dominio @energy4cero.com');
    }
}
