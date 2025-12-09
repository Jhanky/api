<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Battery;

class BatterySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $batteries = [
            // Baterías de GEL
            [
                'brand' => 'Victron Energy',
                'model' => 'OPzS 2V 1000Ah',
                'capacity' => 1000.00,
                'voltage' => 2.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 4500000.00
            ],
            [
                'brand' => 'Victron Energy',
                'model' => 'OPzS 2V 1500Ah',
                'capacity' => 1500.00,
                'voltage' => 2.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 6500000.00
            ],
            [
                'brand' => 'Hoppecke',
                'model' => 'OPzS 2V 800Ah',
                'capacity' => 800.00,
                'voltage' => 2.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 3500000.00
            ],
            [
                'brand' => 'Hoppecke',
                'model' => 'OPzS 2V 1200Ah',
                'capacity' => 1200.00,
                'voltage' => 2.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 5200000.00
            ],
            [
                'brand' => 'Trojan',
                'model' => 'T-105 GEL',
                'capacity' => 225.00,
                'voltage' => 6.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 850000.00
            ],
            [
                'brand' => 'Trojan',
                'model' => 'T-125 GEL',
                'capacity' => 240.00,
                'voltage' => 6.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 950000.00
            ],
            [
                'brand' => 'Rolls',
                'model' => 'S6-460 GEL',
                'capacity' => 460.00,
                'voltage' => 6.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 1800000.00
            ],
            [
                'brand' => 'Rolls',
                'model' => 'S6-530 GEL',
                'capacity' => 530.00,
                'voltage' => 6.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 2100000.00
            ],
            [
                'brand' => 'Victron Energy',
                'model' => 'AGM 12V 200Ah GEL',
                'capacity' => 200.00,
                'voltage' => 12.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 1800000.00
            ],
            [
                'brand' => 'Victron Energy',
                'model' => 'AGM 12V 300Ah GEL',
                'capacity' => 300.00,
                'voltage' => 12.00,
                'type' => 'GEL',
                'technical_sheet_url' => null,
                'price' => 2600000.00
            ],
            
            // Baterías de LITIO
            [
                'brand' => 'Tesla',
                'model' => 'Powerwall 2',
                'capacity' => 13500.00,
                'voltage' => 50.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 35000000.00
            ],
            [
                'brand' => 'LG Chem',
                'model' => 'RESU 10H',
                'capacity' => 9600.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 25000000.00
            ],
            [
                'brand' => 'LG Chem',
                'model' => 'RESU 16H',
                'capacity' => 16000.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 38000000.00
            ],
            [
                'brand' => 'Samsung',
                'model' => 'ESS Home 6.5',
                'capacity' => 6500.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 18000000.00
            ],
            [
                'brand' => 'Samsung',
                'model' => 'ESS Home 10',
                'capacity' => 10000.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 27000000.00
            ],
            [
                'brand' => 'BYD',
                'model' => 'B-Box HV 10.2',
                'capacity' => 10200.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 28000000.00
            ],
            [
                'brand' => 'BYD',
                'model' => 'B-Box HV 15.4',
                'capacity' => 15400.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 42000000.00
            ],
            [
                'brand' => 'Sonnen',
                'model' => 'Eco 10',
                'capacity' => 10000.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 32000000.00
            ],
            [
                'brand' => 'Sonnen',
                'model' => 'Eco 15',
                'capacity' => 15000.00,
                'voltage' => 48.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 46000000.00
            ],
            [
                'brand' => 'Battle Born',
                'model' => 'BB10012 LiFePO4',
                'capacity' => 100.00,
                'voltage' => 12.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 1200000.00
            ],
            [
                'brand' => 'Battle Born',
                'model' => 'BB20012 LiFePO4',
                'capacity' => 200.00,
                'voltage' => 12.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 2200000.00
            ],
            [
                'brand' => 'Renogy',
                'model' => 'RNG-BATT-12-100 LiFePO4',
                'capacity' => 100.00,
                'voltage' => 12.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 900000.00
            ],
            [
                'brand' => 'Renogy',
                'model' => 'RNG-BATT-12-200 LiFePO4',
                'capacity' => 200.00,
                'voltage' => 12.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 1600000.00
            ],
            [
                'brand' => 'VMAXTANKS',
                'model' => 'VMAXSLR125 LiFePO4',
                'capacity' => 125.00,
                'voltage' => 12.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 1100000.00
            ],
            [
                'brand' => 'VMAXTANKS',
                'model' => 'VMAXSLR200 LiFePO4',
                'capacity' => 200.00,
                'voltage' => 12.00,
                'type' => 'LITIO',
                'technical_sheet_url' => null,
                'price' => 1800000.00
            ]
        ];

        foreach ($batteries as $battery) {
            Battery::create($battery);
        }
    }
}
