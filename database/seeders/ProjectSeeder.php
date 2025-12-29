<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectUpmeDetail;
use App\Models\ProjectStateHistory;
use App\Models\ProjectTechnicalSpecs;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure we have users and clients
        if (User::count() == 0) {
            User::factory(5)->create();
        }
        
        if (Client::count() == 0) {
            Client::factory(5)->create();
        }

        // Create 10 Active Projects
        Project::factory()
            ->count(10)
            ->create()
            ->each(function ($project) {
                // Add Technical Specs
                ProjectTechnicalSpecs::factory()->create([
                    'project_id' => $project->id,
                ]);

                // Add UPME Details
                ProjectUpmeDetail::factory()->create([
                    'project_id' => $project->id,
                ]);

                // Add History (1-3 records)
                ProjectStateHistory::factory()->count(rand(1, 3))->create([
                    'project_id' => $project->id,
                ]);

                // Add Documents (3-7 records)
                ProjectDocument::factory()->count(rand(3, 7))->create([
                    'project_id' => $project->id,
                ]);
            });

        // Create 5 Completed Projects
        Project::factory()
            ->count(5)
            ->completed()
            ->create()
            ->each(function ($project) {
                ProjectTechnicalSpecs::factory()->create([
                    'project_id' => $project->id,
                ]);

                ProjectStateHistory::factory()->count(rand(2, 5))->create([
                    'project_id' => $project->id,
                ]);
                
                 ProjectDocument::factory()->count(rand(5, 10))->create([
                    'project_id' => $project->id,
                ]);
            });

        // Create 3 Overdue Projects
        Project::factory()
            ->count(3)
            ->overdue()
            ->create()
            ->each(function ($project) {
                ProjectTechnicalSpecs::factory()->create([
                    'project_id' => $project->id,
                ]);
                
                ProjectStateHistory::factory()->count(rand(1, 3))->create([
                    'project_id' => $project->id,
                ]);

                 ProjectDocument::factory()->count(rand(2, 5))->create([
                    'project_id' => $project->id,
                ]);
            });
    }
}
