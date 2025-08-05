<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Crear roles básicos
        \App\Models\Role::create([
            'name' => 'admin',
            'display_name' => 'Administrador',
            'description' => 'Acceso completo al sistema'
        ]);
        
        \App\Models\Role::create([
            'name' => 'user',
            'display_name' => 'Usuario',
            'description' => 'Usuario básico del sistema'
        ]);
        
        // Crear usuario administrador
        $admin = \App\Models\User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password'),
            'is_active' => true
        ]);
        
        $admin->assignRole('admin');

        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}