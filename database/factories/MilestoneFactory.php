<?php

namespace Database\Factories;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Milestone::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'milestone_type_id' => null,
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'planned_date' => $this->faker->dateTimeBetween('now', '+1 year'),
            'actual_date' => null,
            'status' => 'pending',
            'amount_cop' => $this->faker->randomFloat(2, 1000000, 50000000),
            'requires_verification' => $this->faker->boolean(),
            'verified_by' => null,
            'verified_at' => null,
            'verification_notes' => null,
            'responsible_user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
