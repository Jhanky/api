<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProjectUpmeDetail>
 */
class ProjectUpmeDetailFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'radicado_number' => $this->faker->optional()->bothify('2024#######'),
            'case_number' => $this->faker->optional()->bothify('CAS-#####'),
            'status' => $this->faker->randomElement(['NO_RADICADO', 'RADICADO', 'RESPUESTA_RECIBIDA']),
            'filing_date' => $this->faker->optional()->dateTimeBetween('-6 months', 'now'),
            'consultation_url' => 'https://suu.upme.gov.co/Sis-Usu/',
        ];
    }
}
