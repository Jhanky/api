<?php

namespace Database\Factories;

use App\Models\NetworkOperator;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NetworkOperator>
 */
class NetworkOperatorFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = NetworkOperator::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company() . ' E.S.P.',
            'code' => $this->faker->unique()->bothify('NO-####'),
            'nit' => $this->faker->unique()->numerify('#########-#'),
            'is_active' => true,
        ];
    }
}
