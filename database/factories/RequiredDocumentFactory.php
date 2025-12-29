<?php

namespace Database\Factories;

use App\Models\RequiredDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\RequiredDocument>
 */
class RequiredDocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = RequiredDocument::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'flow_type' => $this->faker->randomElement(['OPERATOR', 'UPME', 'INTERNAL']),
            'state' => $this->faker->word(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'is_required' => $this->faker->boolean(),
            'display_order' => $this->faker->numberBetween(1, 20),
        ];
    }
}
