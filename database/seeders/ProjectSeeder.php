<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\ProjectStateHistory;
use App\Models\ProjectTechnicalSpecs;
use App\Models\ProjectUpmeDetail;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // We will create Projects that match a Quotation and Client correctly.
        
        // 1. Create Projects from new Quotations
        $quotations = Quotation::factory()->count(15)->create([
            'status_id' => 3, // Approved
            'approved_at' => now()->subDays(rand(1, 30)),
        ]);

        foreach ($quotations as $quotation) {
            
            // Create Project linked to this quotation
            // Note: ProjectFactory default definition might try to create a new quotation/client if we don't override.
            // We must override client_id and quotation_id.
            
            $project = Project::factory()->create([
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
                'current_state_id' => rand(1, 4), // Active states
            ]);
            
            // Add related data
            $this->addProjectDetails($project);
        }

        // 2. Create Completed Projects
        $completedQuotations = Quotation::factory()->count(5)->create([
             'status_id' => 3,
             'approved_at' => now()->subMonths(rand(6, 12)),
        ]);

        foreach ($completedQuotations as $quotation) {
             $project = Project::factory()->completed()->create([
                'client_id' => $quotation->client_id,
                'quotation_id' => $quotation->id,
            ]);
            $this->addProjectDetails($project);
        }
    }

    private function addProjectDetails(Project $project): void
    {
        ProjectTechnicalSpecs::factory()->create(['project_id' => $project->id]);
        ProjectUpmeDetail::factory()->create(['project_id' => $project->id]);
        ProjectStateHistory::factory()->count(rand(1, 3))->create(['project_id' => $project->id]);
        ProjectDocument::factory()->count(rand(3, 7))->create(['project_id' => $project->id]);
    }
}
