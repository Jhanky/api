<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\RequiredDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectDocument>
 */
class ProjectDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fileExtensions = ['pdf', 'docx', 'xlsx', 'jpg', 'png'];
        $extension = $this->faker->randomElement($fileExtensions);
        
        return [
            'code' => 'DOC-' . $this->faker->unique()->numberBetween(10000, 99999),
            'project_id' => Project::factory(),
            'required_document_id' => RequiredDocument::inRandomOrder()->first()->id ?? RequiredDocument::factory(),
            'document_type_id' => null, 
            'milestone_id' => Milestone::inRandomOrder()->first()->id ?? Milestone::factory(),
            'name' => $this->faker->words(3, true),
            'original_filename' => $this->faker->word() . '.' . $extension,
            'file_path' => 'projects/documents/' . $this->faker->uuid() . '.' . $extension,
            'mime_type' => 'application/' . $extension,
            'file_size' => $this->faker->numberBetween(1024, 10485760), // 1KB to 10MB
            'file_extension' => $extension,
            'description' => $this->faker->sentence(),
            'document_date' => $this->faker->date(),
            'responsible' => $this->faker->name(),
            'version' => $this->faker->randomDigitNotNull() . '.0',
            'replaces_document_id' => null,
            'is_public' => $this->faker->boolean(20), // 20% public
            'requires_approval' => $this->faker->boolean(30),
            'approved_by' => null,
            'approved_at' => null,
            'uploaded_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the document is approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'requires_approval' => true,
            'approved_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'approved_at' => now(),
        ]);
    }
}
