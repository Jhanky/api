<?php

namespace Database\Factories;

use App\Models\CostCenter;
use App\Models\Invoice;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $amountBeforeIva = $this->faker->randomFloat(2, 500000, 20000000);
        $ivaPercentage = 19.00;
        $ivaAmount = $amountBeforeIva * ($ivaPercentage / 100);
        $totalValue = $amountBeforeIva + $ivaAmount;

        $issueDate = $this->faker->dateTimeBetween('-6 months', 'now');
        $dueDate = (clone $issueDate)->modify('+30 days');

        $status = $this->faker->randomElement(['pendiente', 'pagada', 'parcial', 'anulada']);
        $paymentDate = ($status === 'pagada') ? $this->faker->dateTimeBetween($issueDate, $dueDate) : null;
        $paidAmount = ($status === 'pagada') ? $totalValue : (($status === 'parcial') ? $totalValue / 2 : 0);

        return [
            'invoice_number' => $this->faker->unique()->bothify('INV-#####'),
            'supplier_id' => Supplier::inRandomOrder()->first()->id ?? Supplier::factory(),
            'cost_center_id' => CostCenter::inRandomOrder()->first()->id ?? CostCenter::factory(),
            'amount_before_iva' => $amountBeforeIva,
            'iva_percentage' => $ivaPercentage,
            'total_value' => $totalValue,
            'status' => $status,
            'payment_type' => $this->faker->randomElement(['total', 'parcial']),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'payment_date' => $paymentDate,
            'invoice_file_path' => null,
            'payment_support_path' => null,
            'invoice_file_name' => null,
            'invoice_file_type' => null,
            'invoice_file_size' => null,
            'notes' => $this->faker->sentence(),
            'paid_amount' => $paidAmount,
            'created_by' => User::inRandomOrder()->first()->id ?? 1,
            'updated_by' => User::inRandomOrder()->first()->id ?? 1,
        ];
    }
}
