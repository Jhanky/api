<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\GridType;
use App\Models\Quotation;
use App\Models\QuotationStatus;
use App\Models\SystemType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Quotation>
 */
class QuotationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Quotation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 5000000, 500000000);
        $iva = $subtotal * 0.19;
        
        return [
            'code' => 'QUO-' . $this->faker->unique()->numberBetween(1000, 9999),
            'client_id' => Client::inRandomOrder()->first()->id ?? Client::factory(),
            'user_id' => User::inRandomOrder()->first()->id ?? User::factory(),
            'status_id' => QuotationStatus::inRandomOrder()->first()->id ?? 1,
            'project_name' => $this->faker->sentence(3),
            'system_type_id' => SystemType::inRandomOrder()->first()->id ?? 1,
            'grid_type_id' => GridType::inRandomOrder()->first()->id ?? 1,
            'power_kwp' => $this->faker->randomFloat(2, 5, 200),
            'panel_count' => $this->faker->numberBetween(10, 500),
            'requires_financing' => $this->faker->boolean(),
            'profit_percentage' => 15.00,
            'iva_profit_percentage' => 19.00,
            'commercial_management_percentage' => 5.00,
            'administration_percentage' => 5.00,
            'contingency_percentage' => 3.00,
            'withholding_percentage' => 2.50,
            'subtotal' => $subtotal,
            'profit' => $subtotal * 0.15,
            'profit_iva' => ($subtotal * 0.15) * 0.19,
            'commercial_management' => $subtotal * 0.05,
            'administration' => $subtotal * 0.05,
            'contingency' => $subtotal * 0.03,
            'withholdings' => $subtotal * 0.025,
            'total_value' => $subtotal * 1.3,
            'subtotal2' => $subtotal * 1.15,
            'subtotal3' => $subtotal * 1.25,
            'issue_date' => $this->faker->date(),
            'expiration_date' => $this->faker->dateTimeBetween('now', '+30 days'),
            'approved_at' => null,
            'notes' => $this->faker->optional()->paragraph(),
            'terms_conditions' => $this->faker->optional()->paragraph(),
            'is_active' => true,
        ];
    }
}
