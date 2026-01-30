<?php

namespace Database\Factories;

use App\Models\QuotationItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\QuotationItem>
 */
class QuotationItemFactory extends Factory
{
    protected $model = QuotationItem::class;

    public function definition(): array
    {
        $categories = ['Materiales Eléctricos', 'Estructura', 'Instalación', 'Logística', 'Ingeniería'];
        
        return [
            'description' => $this->faker->sentence(3),
            'category' => $this->faker->randomElement($categories),
            'quantity' => $this->faker->numberBetween(1, 100),
            'unit_measure' => $this->faker->randomElement(['m', 'und', 'gl', 'global']),
            'unit_price_cop' => $this->faker->randomFloat(2, 5000, 1000000),
            'profit_percentage' => 15.00,
            'display_order' => $this->faker->numberBetween(1, 10),
        ];
    }
}
