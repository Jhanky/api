<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Panel;

class PanelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $panels = [
            // Paneles Monocristalinos
            [
                'brand' => 'Canadian Solar',
                'model' => 'CS6K-300MS',
                'power' => 300.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 850000.00
            ],
            [
                'brand' => 'Canadian Solar',
                'model' => 'CS6K-350MS',
                'power' => 350.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 950000.00
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'JKM300M-60',
                'power' => 300.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 820000.00
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'JKM350M-60',
                'power' => 350.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 920000.00
            ],
            [
                'brand' => 'Longi Solar',
                'model' => 'LR4-60HPB-300M',
                'power' => 300.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 800000.00
            ],
            [
                'brand' => 'Longi Solar',
                'model' => 'LR4-60HPB-350M',
                'power' => 350.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 900000.00
            ],
            [
                'brand' => 'Trina Solar',
                'model' => 'TSM-300DE14',
                'power' => 300.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 870000.00
            ],
            [
                'brand' => 'Trina Solar',
                'model' => 'TSM-350DE14',
                'power' => 350.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 970000.00
            ],
            
            // Paneles Policristalinos
            [
                'brand' => 'Canadian Solar',
                'model' => 'CS6P-250P',
                'power' => 250.00,
                'type' => 'Policristalino',
                'technical_sheet_url' => null,
                'price' => 650000.00
            ],
            [
                'brand' => 'Canadian Solar',
                'model' => 'CS6P-275P',
                'power' => 275.00,
                'type' => 'Policristalino',
                'technical_sheet_url' => null,
                'price' => 720000.00
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'JKP250P-60',
                'power' => 250.00,
                'type' => 'Policristalino',
                'technical_sheet_url' => null,
                'price' => 620000.00
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'JKP275P-60',
                'power' => 275.00,
                'type' => 'Policristalino',
                'technical_sheet_url' => null,
                'price' => 690000.00
            ],
            
            // Paneles de Alta Eficiencia
            [
                'brand' => 'SunPower',
                'model' => 'SPR-E20-327',
                'power' => 327.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 1200000.00
            ],
            [
                'brand' => 'SunPower',
                'model' => 'SPR-E20-435',
                'power' => 435.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 1500000.00
            ],
            [
                'brand' => 'LG Solar',
                'model' => 'LG350N1C-A5',
                'power' => 350.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 1100000.00
            ],
            [
                'brand' => 'LG Solar',
                'model' => 'LG400N1C-A5',
                'power' => 400.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 1250000.00
            ],
            
            // Paneles Bifaciales
            [
                'brand' => 'Longi Solar',
                'model' => 'LR4-72HBD-400M',
                'power' => 400.00,
                'type' => 'Bifacial',
                'technical_sheet_url' => null,
                'price' => 1050000.00
            ],
            [
                'brand' => 'Longi Solar',
                'model' => 'LR4-72HBD-450M',
                'power' => 450.00,
                'type' => 'Bifacial',
                'technical_sheet_url' => null,
                'price' => 1180000.00
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'JKM400M-72',
                'power' => 400.00,
                'type' => 'Bifacial',
                'technical_sheet_url' => null,
                'price' => 1020000.00
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'JKM450M-72',
                'power' => 450.00,
                'type' => 'Bifacial',
                'technical_sheet_url' => null,
                'price' => 1150000.00
            ],
            
            // Paneles de Bajo Costo
            [
                'brand' => 'Risen Energy',
                'model' => 'RSM120-6-300M',
                'power' => 300.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 750000.00
            ],
            [
                'brand' => 'Risen Energy',
                'model' => 'RSM120-6-350M',
                'power' => 350.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 850000.00
            ],
            [
                'brand' => 'JA Solar',
                'model' => 'JAM60S10-300',
                'power' => 300.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 760000.00
            ],
            [
                'brand' => 'JA Solar',
                'model' => 'JAM60S10-350',
                'power' => 350.00,
                'type' => 'Monocristalino',
                'technical_sheet_url' => null,
                'price' => 860000.00
            ]
        ];

        foreach ($panels as $panel) {
            Panel::create($panel);
        }
    }
}
