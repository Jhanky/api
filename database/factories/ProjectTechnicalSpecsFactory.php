<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectTechnicalSpecs;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectTechnicalSpecs>
 */
class ProjectTechnicalSpecsFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ProjectTechnicalSpecs::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $panelPower = $this->faker->randomElement([450, 500, 550, 600]);
        $panelCount = $this->faker->numberBetween(10, 500);
        $installedPower = ($panelCount * $panelPower) / 1000;

        return [
            'project_id' => Project::factory(),
            'electrical_specs' => json_encode([
                'installed_power_kwp' => $installedPower,
                'panel_count' => $panelCount,
                'panel_brand' => $this->faker->company(),
                'panel_model' => 'SolarPanel-' . $this->faker->bothify('##??'),
                'panel_power_watts' => $panelPower,
                'inverter_count' => $this->faker->numberBetween(1, 10),
                'inverter_brand' => $this->faker->company(),
                'inverter_model' => 'Inverter-' . $this->faker->bothify('##??'),
                'inverter_power_kw' => $this->faker->randomElement([5, 10, 20, 50, 100]),
                'battery_count' => $this->faker->numberBetween(0, 20),
                'battery_brand' => $this->faker->optional()->company(),
                'battery_model' => $this->faker->optional()->bothify('BAT-##??'),
                'battery_capacity_kwh' => $this->faker->optional()->randomFloat(2, 5, 20),
                'estimated_monthly_generation_kwh' => $installedPower * 4 * 30,
                'estimated_annual_generation_kwh' => $installedPower * 4 * 365,
                'system_type' => $this->faker->randomElement(['on-grid', 'off-grid', 'hybrid']),
                'grid_type' => $this->faker->randomElement(['monophasic', 'biphasic', 'triphasic']),
            ]),
            'structural_specs' => json_encode([
                'structure_type' => $this->faker->randomElement(['roof', 'ground', 'carport']),
                'installation_type' => $this->faker->randomElement(['residential', 'commercial', 'industrial']),
                'tilt_angle' => $this->faker->randomFloat(1, 5, 20),
                'azimuth' => $this->faker->randomFloat(1, 0, 360),
            ]),
            'environmental_conditions' => json_encode([
                'temperature_min' => $this->faker->numberBetween(10, 20),
                'temperature_max' => $this->faker->numberBetween(25, 35),
                'irradiation' => $this->faker->randomFloat(2, 3, 6),
            ]),
            'regulatory_compliance' => json_encode([
                'retie_certified' => $this->faker->boolean(),
                'retie_date' => $this->faker->date(),
            ]),
            'technical_notes' => $this->faker->optional()->paragraph(),
            'updated_by' => User::inRandomOrder()->first()->id ?? User::factory(),
        ];
    }
}
