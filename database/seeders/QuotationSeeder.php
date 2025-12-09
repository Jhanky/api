<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Quotation;
use App\Models\UsedProduct;
use App\Models\ItemCotizacion;
use App\Models\Client;
use App\Models\User;
use App\Models\Panel;
use App\Models\Inverter;
use App\Models\Battery;
use App\Models\QuotationStatus;

class QuotationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener datos necesarios
        $clients = Client::all();
        $users = User::all();
        $panels = Panel::all();
        $inverters = Inverter::all();
        $batteries = Battery::all();
        $statuses = QuotationStatus::all();

        if ($clients->isEmpty() || $users->isEmpty() || $panels->isEmpty() || $inverters->isEmpty()) {
            $this->command->info('No se pueden crear cotizaciones sin clientes, usuarios, paneles o inversores.');
            return;
        }

        $quotations = [
            [
                'client_id' => $clients->first()->client_id,
                'user_id' => $users->first()->id,
                'project_name' => 'Sistema Residencial San Salvador',
                'system_type' => 'On-grid',
                'power_kwp' => 5.0,
                'panel_count' => 12,
                'requires_financing' => false,
                'profit_percentage' => 15.000,
                'iva_profit_percentage' => 13.000,
                'commercial_management_percentage' => 5.000,
                'administration_percentage' => 3.000,
                'contingency_percentage' => 2.000,
                'withholding_percentage' => 2.000,
                'status_id' => $statuses->where('name', 'Diseñada')->first()->status_id
            ],
            [
                'client_id' => $clients->first()->client_id,
                'user_id' => $users->first()->id,
                'project_name' => 'Sistema Comercial Centro Comercial',
                'system_type' => 'Híbrido',
                'power_kwp' => 15.0,
                'panel_count' => 36,
                'requires_financing' => true,
                'profit_percentage' => 20.000,
                'iva_profit_percentage' => 13.000,
                'commercial_management_percentage' => 8.000,
                'administration_percentage' => 5.000,
                'contingency_percentage' => 3.000,
                'withholding_percentage' => 2.500,
                'status_id' => $statuses->where('name', 'Enviada')->first()->status_id
            ],
            [
                'client_id' => $clients->first()->client_id,
                'user_id' => $users->first()->id,
                'project_name' => 'Sistema Industrial Fábrica Textil',
                'system_type' => 'Off-grid',
                'power_kwp' => 25.0,
                'panel_count' => 60,
                'requires_financing' => true,
                'profit_percentage' => 25.000,
                'iva_profit_percentage' => 13.000,
                'commercial_management_percentage' => 10.000,
                'administration_percentage' => 7.000,
                'contingency_percentage' => 5.000,
                'withholding_percentage' => 3.000,
                'status_id' => $statuses->where('name', 'Negociaciones')->first()->status_id
            ]
        ];

        foreach ($quotations as $quotationData) {
            $quotation = Quotation::create($quotationData);

            // Agregar productos utilizados
            $this->addUsedProducts($quotation, $panels, $inverters, $batteries);
            
            // Agregar items adicionales
            $this->addItems($quotation);
            
            // Calcular totales
            $quotation->calculateTotals();
        }
    }

    private function addUsedProducts($quotation, $panels, $inverters, $batteries)
    {
        // Agregar paneles
        $panel = $panels->random();
        $panelQuantity = $quotation->panel_count;
        
        UsedProduct::create([
            'quotation_id' => $quotation->quotation_id,
            'product_type' => 'panel',
            'product_id' => $panel->panel_id,
            'quantity' => $panelQuantity,
            'unit_price' => $panel->price,
            'partial_value' => $panelQuantity * $panel->price,
            'profit_percentage' => 15.00,
            'profit' => ($panelQuantity * $panel->price) * 0.15,
            'total_value' => ($panelQuantity * $panel->price) * 1.15
        ]);

        // Agregar inversor
        $inverter = $inverters->where('power', '>=', $quotation->power_kwp * 1000)->first() ?? $inverters->random();
        
        UsedProduct::create([
            'quotation_id' => $quotation->quotation_id,
            'product_type' => 'inverter',
            'product_id' => $inverter->inverter_id,
            'quantity' => 1,
            'unit_price' => $inverter->price,
            'partial_value' => $inverter->price,
            'profit_percentage' => 20.00,
            'profit' => $inverter->price * 0.20,
            'total_value' => $inverter->price * 1.20
        ]);

        // Agregar baterías si es sistema híbrido u off-grid
        if (in_array($quotation->system_type, ['Híbrido', 'Off-grid'])) {
            $battery = $batteries->random();
            $batteryQuantity = ceil($quotation->power_kwp / 5); // 1 batería por cada 5 kWp aproximadamente
            
            UsedProduct::create([
                'quotation_id' => $quotation->quotation_id,
                'product_type' => 'battery',
                'product_id' => $battery->battery_id,
                'quantity' => $batteryQuantity,
                'unit_price' => $battery->price,
                'partial_value' => $batteryQuantity * $battery->price,
                'profit_percentage' => 25.00,
                'profit' => ($batteryQuantity * $battery->price) * 0.25,
                'total_value' => ($batteryQuantity * $battery->price) * 1.25
            ]);
        }
    }

    private function addItems($quotation)
    {
        $items = [
            [
                'description' => 'Estructura de montaje para paneles solares',
                'item_type' => 'Materiales',
                'quantity' => $quotation->panel_count,
                'unit' => 'Unidades',
                'unit_price' => 150000.00,
                'profit_percentage' => 15.00
            ],
            [
                'description' => 'Cableado DC y AC para sistema solar',
                'item_type' => 'Materiales',
                'quantity' => $quotation->power_kwp * 50, // 50 metros por kWp
                'unit' => 'Metros',
                'unit_price' => 2500.00,
                'profit_percentage' => 20.00
            ],
            [
                'description' => 'Instalación y montaje del sistema',
                'item_type' => 'Mano de obra',
                'quantity' => $quotation->power_kwp * 8, // 8 horas por kWp
                'unit' => 'Horas',
                'unit_price' => 25000.00,
                'profit_percentage' => 30.00
            ],
            [
                'description' => 'Diseño técnico y planos del proyecto',
                'item_type' => 'Servicios',
                'quantity' => 1,
                'unit' => 'Proyecto',
                'unit_price' => 500000.00,
                'profit_percentage' => 25.00
            ]
        ];

        foreach ($items as $itemData) {
            $partialValue = $itemData['quantity'] * $itemData['unit_price'];
            $profit = $partialValue * ($itemData['profit_percentage'] / 100);
            $totalValue = $partialValue + $profit;

            ItemCotizacion::create([
                'quotation_id' => $quotation->quotation_id,
                'description' => $itemData['description'],
                'item_type' => $itemData['item_type'],
                'quantity' => $itemData['quantity'],
                'unit' => $itemData['unit'],
                'unit_price' => $itemData['unit_price'],
                'partial_value' => $partialValue,
                'profit_percentage' => $itemData['profit_percentage'],
                'profit' => $profit,
                'total_value' => $totalValue
            ]);
        }
    }
}
