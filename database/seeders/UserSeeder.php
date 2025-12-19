<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario administrador
        $adminUser = User::create([
            'username' => 'admin',
            'name' => 'Administrador',
            'email' => 'admin@energy4cero.com',
            'password' => Hash::make('password'), // Cambiar en producción
            'is_active' => true,
        ]);

        // Asignar rol de administrador
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminUser->roles()->attach($adminRole->id, [
                'assigned_by' => $adminUser->id,
                'assigned_at' => now()
            ]);
        }
    }
}
