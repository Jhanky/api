<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Project;
use App\Models\ProjectState;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startDate = $this->faker->dateTimeBetween('-1 year', 'now');
        $estimatedEndDate = $this->faker->dateTimeBetween($startDate, '+1 year');
        
        // Prepare optional actual end date (50% chance if start date is older than 6 months)
        $actualEndDate = null;
        if ($startDate < now()->subMonths(6) && $this->faker->boolean(50)) {
            $actualEndDate = $this->faker->dateTimeBetween($startDate, $estimatedEndDate);
        }

        $contractedValue = $this->faker->randomFloat(2, 5000000, 500000000);

        return [
            'code' => 'PROJ-' . $this->faker->unique()->numberBetween(1000, 9999),
            // Relationships are typically overridden in seeder or state methods, 
            // but we provide defaults here to avoid errors if used standalone.
            // Using factory() here would create new related records for each project,
            // which might clutter the DB. Using random existing ID is risky if table empty.
            // Best practice: Use factory() or allow seeder to override. We'll use factory().
            'client_id' => Client::inRandomOrder()->first()->id ?? Client::factory(), 
            'quotation_id' => Quotation::inRandomOrder()->first()->id ?? Quotation::factory(), 
            'current_state_id' => ProjectState::inRandomOrder()->first()->id ?? 1, 
            'name' => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'installation_address' => $this->faker->address(),
            'coordinates' => $this->faker->latitude() . ',' . $this->faker->longitude(),
            'start_date' => $startDate,
            'estimated_end_date' => $estimatedEndDate,
            'actual_end_date' => $actualEndDate,
            'contracted_value_cop' => $contractedValue,
            'total_cost_cop' => $contractedValue * $this->faker->randomFloat(2, 0.6, 0.9), // 10-40% profit margin
            'project_manager_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'technical_leader_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'priority' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'notes' => $this->faker->optional()->paragraph(),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the project is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'actual_end_date' => $this->faker->dateTimeBetween($attributes['start_date'], 'now'),
            'current_state_id' => ProjectState::where('name', 'Completed')->first()->id ?? 5, // Improve dynamic lookup
        ]);
    }

    /**
     * Indicate that the project is overdue.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_end_date' => $this->faker->dateTimeBetween('-1 year', '-1 day'),
            'actual_end_date' => null,
        ]);
    }
}
