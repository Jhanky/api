<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ClientTypeSeeder::class,
            ClientSeeder::class,
            GridTypeSeeder::class,
            SystemTypeSeeder::class,
            QuotationStatusSeeder::class,
            ProjectStateSeeder::class,
            PanelSeeder::class,
            BatterySeeder::class,
            InverterSeeder::class,
            ProjectManagementSeeder::class,
        ]);
    }
}
