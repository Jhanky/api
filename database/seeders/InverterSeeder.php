<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Inverter;

class InverterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inverters = [
            // Inversores On-Grid Monofásico 110V
            [
                'brand' => 'SMA',
                'model' => 'Sunny Boy 3.0',
                'power' => 3000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 4500000.00
            ],
            [
                'brand' => 'SMA',
                'model' => 'Sunny Boy 5.0',
                'power' => 5000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 6800000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Primo 3.0-1',
                'power' => 3000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 4200000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Primo 5.0-1',
                'power' => 5000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 6500000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-3KTL-L1',
                'power' => 3000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 3600000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-5KTL-L1',
                'power' => 5000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 5300000.00
            ],
            
            // Inversores On-Grid Bifásico 220V
            [
                'brand' => 'SMA',
                'model' => 'Sunny Boy 6.0',
                'power' => 6000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Bifásico 220V',
                'technical_sheet_url' => null,
                'price' => 8200000.00
            ],
            [
                'brand' => 'SMA',
                'model' => 'Sunny Boy 8.0',
                'power' => 8000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Bifásico 220V',
                'technical_sheet_url' => null,
                'price' => 10500000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Primo 6.0-1',
                'power' => 6000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Bifásico 220V',
                'technical_sheet_url' => null,
                'price' => 7800000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Primo 8.0-1',
                'power' => 8000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Bifásico 220V',
                'technical_sheet_url' => null,
                'price' => 9800000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-6KTL-L1',
                'power' => 6000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Bifásico 220V',
                'technical_sheet_url' => null,
                'price' => 6400000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-8KTL-L1',
                'power' => 8000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Bifásico 220V',
                'technical_sheet_url' => null,
                'price' => 8400000.00
            ],
            
            // Inversores On-Grid Trifásico 220V
            [
                'brand' => 'SMA',
                'model' => 'Sunny Tripower 8.0',
                'power' => 8000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 10500000.00
            ],
            [
                'brand' => 'SMA',
                'model' => 'Sunny Tripower 10.0',
                'power' => 10000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 13200000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Symo 8.0-3-M',
                'power' => 8000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 9800000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Symo 10.0-3-M',
                'power' => 10000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 12200000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-8KTL-M0',
                'power' => 8000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 8400000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-10KTL-M0',
                'power' => 10000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 10500000.00
            ],
            
            // Inversores On-Grid Trifásico 440V
            [
                'brand' => 'SMA',
                'model' => 'Sunny Tripower 15.0',
                'power' => 15000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 440V',
                'technical_sheet_url' => null,
                'price' => 18500000.00
            ],
            [
                'brand' => 'SMA',
                'model' => 'Sunny Tripower 20.0',
                'power' => 20000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 440V',
                'technical_sheet_url' => null,
                'price' => 24500000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Symo 15.0-3-M',
                'power' => 15000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 440V',
                'technical_sheet_url' => null,
                'price' => 17500000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Symo 20.0-3-M',
                'power' => 20000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 440V',
                'technical_sheet_url' => null,
                'price' => 23000000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-15KTL-M0',
                'power' => 15000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 440V',
                'technical_sheet_url' => null,
                'price' => 15500000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-20KTL-M0',
                'power' => 20000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásico 440V',
                'technical_sheet_url' => null,
                'price' => 20500000.00
            ],
            
            // Inversores Off-Grid
            [
                'brand' => 'Victron Energy',
                'model' => 'Phoenix Inverter 12/1200',
                'power' => 1200.00,
                'system_type' => 'Off-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 3000000.00
            ],
            [
                'brand' => 'Victron Energy',
                'model' => 'Phoenix Inverter 24/2000',
                'power' => 2000.00,
                'system_type' => 'Off-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 4500000.00
            ],
            [
                'brand' => 'Victron Energy',
                'model' => 'Phoenix Inverter 48/3000',
                'power' => 3000.00,
                'system_type' => 'Off-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 6800000.00
            ],
            [
                'brand' => 'OutBack Power',
                'model' => 'Radian GS4048A',
                'power' => 4000.00,
                'system_type' => 'Off-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 9500000.00
            ],
            [
                'brand' => 'OutBack Power',
                'model' => 'Radiance GS8048A',
                'power' => 8000.00,
                'system_type' => 'Off-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 16800000.00
            ],
            
            // Inversores Híbridos
            [
                'brand' => 'SMA',
                'model' => 'Sunny Boy Storage 3.7',
                'power' => 3700.00,
                'system_type' => 'Híbrido',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 8200000.00
            ],
            [
                'brand' => 'SMA',
                'model' => 'Sunny Boy Storage 5.0',
                'power' => 5000.00,
                'system_type' => 'Híbrido',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 10500000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Symo Hybrid 5.0-3-S',
                'power' => 5000.00,
                'system_type' => 'Híbrido',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 12200000.00
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Symo Hybrid 8.0-3-S',
                'power' => 8000.00,
                'system_type' => 'Híbrido',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 15800000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-5KTL-M1',
                'power' => 5000.00,
                'system_type' => 'Híbrido',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 6800000.00
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-8KTL-M1',
                'power' => 8000.00,
                'system_type' => 'Híbrido',
                'grid_type' => 'Trifásico 220V',
                'technical_sheet_url' => null,
                'price' => 9800000.00
            ],
            
            // Inversores de Bajo Costo
            [
                'brand' => 'Growatt',
                'model' => 'MIN 3000TL-X',
                'power' => 3000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 2800000.00
            ],
            [
                'brand' => 'Growatt',
                'model' => 'MIN 5000TL-X',
                'power' => 5000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 4200000.00
            ],
            [
                'brand' => 'Solaredge',
                'model' => 'SE3000H',
                'power' => 3000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 3400000.00
            ],
            [
                'brand' => 'Solaredge',
                'model' => 'SE5000H',
                'power' => 5000.00,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásico 110V',
                'technical_sheet_url' => null,
                'price' => 4900000.00
            ]
        ];

        foreach ($inverters as $inverter) {
            Inverter::create($inverter);
        }
    }
}
