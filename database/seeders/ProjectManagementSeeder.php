<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ProjectManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            // Ensure dependencies are seeded first
            ProjectStateSeeder::class, 
            ProjectSeeder::class,
        ]);
    }
}
