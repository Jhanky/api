<?php

namespace Database\Factories;

use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CostCenter>
 */
class CostCenterFactory extends Factory
{
    protected $model = CostCenter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->bothify('CC-####'),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'department_id' => Department::inRandomOrder()->first()->id ?? 1,
            'project_id' => $this->faker->boolean(70) ? (Project::inRandomOrder()->first()->id ?? null) : null,
            'is_active' => true,
        ];
    }
}
