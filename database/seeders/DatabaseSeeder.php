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
            GeographySeeder::class, // New: Departments and Cities
            ClientTypeSeeder::class,
            ClientSeeder::class,
            GridTypeSeeder::class,
            SystemTypeSeeder::class,
            QuotationStatusSeeder::class,
            ProjectStateSeeder::class,
            PanelSeeder::class,
            BatterySeeder::class,
            InverterSeeder::class,
            SupplierSeeder::class, // New
            QuotationSeeder::class, // New
            RequiredDocumentSeeder::class, // New
            ProjectSeeder::class,   // Updated
            CostCenterSeeder::class, // New
            InvoiceSeeder::class,   // New
        ]);
    }
}
