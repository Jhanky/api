<?php

namespace Database\Factories;

use App\Models\QuotationProduct;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuotationProduct>
 */
class QuotationProductFactory extends Factory
{
    protected $model = QuotationProduct::class;

    public function definition(): array
    {
        $types = ['panel', 'inverter', 'battery'];
        $type = $this->faker->randomElement($types);
        
        return [
            'product_type' => $type,
            'product_id' => $this->faker->numberBetween(1, 10), // Assuming seeders create ids 1-10
            'snapshot_brand' => $this->faker->company(),
            'snapshot_model' => $this->faker->bothify('MOD-####'),
            'snapshot_specs' => ['power' => $this->faker->numberBetween(300, 600) . 'W'],
            'quantity' => $this->faker->numberBetween(1, 20),
            'unit_price_cop' => $this->faker->randomFloat(2, 500000, 5000000),
            'profit_percentage' => 15.00,
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
    
    // States for specific product types
    public function panel()
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'panel',
            'unit_price_cop' => $this->faker->randomFloat(2, 600000, 1200000),
        ]);
    }

    public function inverter()
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'inverter',
            'unit_price_cop' => $this->faker->randomFloat(2, 2500000, 6000000),
        ]);
    }

    public function battery()
    {
        return $this->state(fn (array $attributes) => [
            'product_type' => 'battery',
            'unit_price_cop' => $this->faker->randomFloat(2, 10000000, 30000000),
        ]);
    }
}
