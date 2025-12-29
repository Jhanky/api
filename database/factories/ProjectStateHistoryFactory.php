<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectState;
use App\Models\ProjectStateHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectStateHistory>
 */
class ProjectStateHistoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectStateHistory::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $changedAt = $this->faker->dateTimeBetween('-1 year', 'now');
        
        return [
            'project_id' => Project::factory(),
            'from_state_id' => ProjectState::inRandomOrder()->first()->id ?? 1,
            'to_state_id' => ProjectState::inRandomOrder()->first()->id ?? 2,
            'reason' => $this->faker->sentence(),
            'notes' => $this->faker->optional()->sentence(),
            'changed_by' => User::inRandomOrder()->first()->id ?? User::factory(),
            'changed_at' => $changedAt,
            'started_at' => $changedAt,
            'ended_at' => $this->faker->optional()->dateTimeBetween($changedAt, 'now'),
            'duration_days' => $this->faker->optional()->numberBetween(1, 30),
        ];
    }
}
