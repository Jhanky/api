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
            [
                'brand' => 'SMA',
                'model' => 'Sunny Tripower 5000TL',
                'power' => 5,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásica 220V',
                'price' => 3800000,
            ],
            [
                'brand' => 'Fronius',
                'model' => 'Primo 5.0-1',
                'power' => 5,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásica',
                'price' => 3100000,
            ],
            [
                'brand' => 'Huawei',
                'model' => 'SUN2000-5KTL-M0',
                'power' => 5,
                'system_type' => 'Híbrido',
                'grid_type' => 'Trifásica 220V',
                'price' => 2750000,
            ],
            [
                'brand' => 'Sungrow',
                'model' => 'SG5.0RS',
                'power' => 5,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásica',
                'price' => 2400000,
            ],
            [
                'brand' => 'Growatt',
                'model' => 'MIN 5000TL-X',
                'power' => 5,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásica 220V',
                'price' => 2100000,
            ],
            [
                'brand' => 'Victron Energy',
                'model' => 'MultiPlus 5000',
                'power' => 5,
                'system_type' => 'Off-grid',
                'grid_type' => 'Monofásica',
                'price' => 4800000,
            ],
            [
                'brand' => 'Solis',
                'model' => 'S5-GR1P5K',
                'power' => 1.5,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásica',
                'price' => 850000,
            ],
            [
                'brand' => 'GoodWe',
                'model' => 'GW5048D-ES',
                'power' => 5,
                'system_type' => 'Híbrido',
                'grid_type' => 'Trifásica 220V',
                'price' => 3300000,
            ],
            [
                'brand' => 'Deye',
                'model' => 'SUN-5K-SG01LP1-EU',
                'power' => 5,
                'system_type' => 'Híbrido',
                'grid_type' => 'Monofásica',
                'price' => 2950000,
            ],
            [
                'brand' => 'Ingeteam',
                'model' => 'Ingecon Sun 5TL',
                'power' => 5,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásica 220V',
                'price' => 3600000,
            ],
            [
                'brand' => 'Kaco',
                'model' => 'Powador 5000',
                'power' => 5,
                'system_type' => 'On-grid',
                'grid_type' => 'Monofásica',
                'price' => 3350000,
            ],
            [
                'brand' => 'ABB',
                'model' => 'TRIO-5.8-TL-OUTD',
                'power' => 5.8,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásica 220V',
                'price' => 4300000,
            ],
            // Agregando más variedad con diferentes tipos de red
            [
                'brand' => 'Schneider Electric',
                'model' => 'Conext Core XC680',
                'power' => 6.8,
                'system_type' => 'Híbrido',
                'grid_type' => 'Trifásica 440V',
                'price' => 5500000,
            ],
            [
                'brand' => 'OutBack Power',
                'model' => 'GS8048A',
                'power' => 8,
                'system_type' => 'Off-grid',
                'grid_type' => 'Bifásica 220V',
                'price' => 6000000,
            ],
            [
                'brand' => 'Delta Electronics',
                'model' => 'RPI M6A',
                'power' => 6,
                'system_type' => 'On-grid',
                'grid_type' => 'Trifásica 440V',
                'price' => 4800000,
            ],
        ];

        foreach ($inverters as $inverter) {
            Inverter::create($inverter);
        }
    }
}
