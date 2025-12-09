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
        // Ejecutar seeders en orden
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            LocationSeeder::class,
            PanelSeeder::class,
            InverterSeeder::class,
            BatterySeeder::class,
            QuotationStatusSeeder::class,
            QuotationSeeder::class,
            ProviderSeeder::class,
            CostCenterSeeder::class,
            InvoiceSeeder::class,
        ]);

        // Asignar roles a los usuarios creados
        $admin = \App\Models\User::where('email', 'admin@energy4cero.com')->first();
        if ($admin) {
            $admin->assignRole('administrador');
        }

        $juan = \App\Models\User::where('email', 'juan.perez@energy4cero.com')->first();
        if ($juan) {
            $juan->assignRole('comercial');
        }

        $maria = \App\Models\User::where('email', 'maria.garcia@energy4cero.com')->first();
        if ($maria) {
            $maria->assignRole('tecnico');
        }
    }
}