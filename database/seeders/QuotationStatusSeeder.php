<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\QuotationStatus;

class QuotationStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen estados para evitar duplicados
        if (QuotationStatus::count() > 0) {
            return;
        }

        $statuses = [
            [
                'name' => 'Borrador',
                'slug' => 'draft',
                'description' => 'Cotización en edición',
                'color' => '#94a3b8',
                'display_order' => 1,
                'is_final' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Enviada',
                'slug' => 'sent',
                'description' => 'Enviada al cliente',
                'color' => '#3b82f6',
                'display_order' => 2,
                'is_final' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Aprobada',
                'slug' => 'approved',
                'description' => 'Aceptada por el cliente',
                'color' => '#16a34a',
                'display_order' => 3,
                'is_final' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Rechazada',
                'slug' => 'rejected',
                'description' => 'Rechazada por el cliente',
                'color' => '#dc2626',
                'display_order' => 4,
                'is_final' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Vencida',
                'slug' => 'expired',
                'description' => 'No respondida a tiempo',
                'color' => '#f97316',
                'display_order' => 5,
                'is_final' => true,
                'is_active' => true,
            ],
        ];

        foreach ($statuses as $status) {
            QuotationStatus::create($status);
        }
    }
}
