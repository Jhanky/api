<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Client::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->company(),
            'client_type_id' => ClientType::inRandomOrder()->first()->id ?? 1,
            'document_type' => $this->faker->randomElement(['CC', 'NIT', 'CE']),
            'document_number' => $this->faker->unique()->numerify('#########'),
            'nic' => $this->faker->unique()->numerify('#######'),
            'email' => $this->faker->unique()->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'mobile' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'department_id' => Department::inRandomOrder()->first()->id ?? 1,
            'city_id' => City::inRandomOrder()->first()->id ?? 1,
            'monthly_consumption_kwh' => $this->faker->randomFloat(2, 100, 5000),
            'tariff_cop_kwh' => $this->faker->randomFloat(2, 500, 1000),
            'responsible_user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'notes' => $this->faker->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
