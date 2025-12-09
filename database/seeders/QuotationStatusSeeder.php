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
        $statuses = [
            [
                'name' => 'Pendiente',
                'description' => 'Cotización en estado inicial, pendiente de diseño y desarrollo',
                'color' => '#F59E0B', // Amarillo
                'is_active' => true
            ],
            [
                'name' => 'Diseñada',
                'description' => 'Cotización con diseño técnico completo y especificaciones definidas',
                'color' => '#3B82F6', // Azul
                'is_active' => true
            ],
            [
                'name' => 'Enviada',
                'description' => 'Cotización enviada al cliente para su revisión y consideración',
                'color' => '#10B981', // Verde
                'is_active' => true
            ],
            [
                'name' => 'Negociaciones',
                'description' => 'Cotización en proceso de negociación con el cliente',
                'color' => '#8B5CF6', // Púrpura
                'is_active' => true
            ],
            [
                'name' => 'Contratada',
                'description' => 'Cotización aceptada y convertida en contrato',
                'color' => '#059669', // Verde oscuro
                'is_active' => true
            ],
            [
                'name' => 'Descartada',
                'description' => 'Cotización rechazada o descartada por el cliente',
                'color' => '#EF4444', // Rojo
                'is_active' => true
            ]
        ];

        foreach ($statuses as $status) {
            QuotationStatus::create($status);
        }
    }
}
