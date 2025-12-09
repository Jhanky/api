<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener algunos proveedores y centros de costo para crear facturas
        $providers = \App\Models\Provider::take(5)->get();
        $costCenters = \App\Models\CostCenter::take(5)->get();

        if ($providers->isEmpty() || $costCenters->isEmpty()) {
            return; // No crear facturas si no hay proveedores o centros de costo
        }

        $invoices = [
            [
                'invoice_number' => 'FAC-001-2024',
                'invoice_date' => now()->subDays(30),
                'due_date' => now()->subDays(15),
                'total_amount' => 1500000.00,
                'description' => 'Compra de paneles solares para proyecto residencial',
                'status' => 'PAGADA',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ],
            [
                'invoice_number' => 'FAC-002-2024',
                'invoice_date' => now()->subDays(25),
                'due_date' => now()->subDays(10),
                'total_amount' => 2300000.00,
                'description' => 'Servicios de instalación de sistema solar industrial',
                'status' => 'PAGADA',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ],
            [
                'invoice_number' => 'FAC-003-2024',
                'invoice_date' => now()->subDays(20),
                'due_date' => now()->subDays(5),
                'total_amount' => 850000.00,
                'description' => 'Mantenimiento preventivo de equipos solares',
                'status' => 'PENDIENTE',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ],
            [
                'invoice_number' => 'FAC-004-2024',
                'invoice_date' => now()->subDays(15),
                'due_date' => now()->addDays(5),
                'total_amount' => 3200000.00,
                'description' => 'Compra de inversores y equipos de monitoreo',
                'status' => 'PENDIENTE',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ],
            [
                'invoice_number' => 'FAC-005-2024',
                'invoice_date' => now()->subDays(10),
                'due_date' => now()->addDays(10),
                'total_amount' => 1800000.00,
                'description' => 'Baterías de respaldo para sistema híbrido',
                'status' => 'PENDIENTE',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ],
            [
                'invoice_number' => 'FAC-006-2024',
                'invoice_date' => now()->subDays(5),
                'due_date' => now()->addDays(15),
                'total_amount' => 2750000.00,
                'description' => 'Materiales eléctricos y cableado especializado',
                'status' => 'PENDIENTE',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ],
            [
                'invoice_number' => 'FAC-007-2024',
                'invoice_date' => now()->subDays(2),
                'due_date' => now()->addDays(18),
                'total_amount' => 1200000.00,
                'description' => 'Consultoría técnica para optimización de sistemas',
                'status' => 'PENDIENTE',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ],
            [
                'invoice_number' => 'FAC-008-2024',
                'invoice_date' => now()->subDays(1),
                'due_date' => now()->addDays(20),
                'total_amount' => 4500000.00,
                'description' => 'Proyecto completo de energía solar para edificio comercial',
                'status' => 'PENDIENTE',
                'provider_id' => $providers->random()->provider_id,
                'cost_center_id' => $costCenters->random()->cost_center_id
            ]
        ];

        foreach ($invoices as $invoice) {
            \App\Models\Invoice::create($invoice);
        }
    }
}
