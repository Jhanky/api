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
            [
                'brand' => 'Tesla',
                'model' => 'Powerwall 2',
                'capacity' => 13.5,
                'voltage' => 400,
                'type' => 'litio',
                'price' => 28000000,
            ],
            [
                'brand' => 'LG',
                'model' => 'RESU 10H',
                'capacity' => 9.8,
                'voltage' => 400,
                'type' => 'litio',
                'price' => 16500000,
            ],
            [
                'brand' => 'BYD',
                'model' => 'Battery-Box Premium HVS',
                'capacity' => 10.2,
                'voltage' => 400,
                'type' => 'litio',
                'price' => 20000000,
            ],
            [
                'brand' => 'Sungrow',
                'model' => 'SBR128',
                'capacity' => 128,
                'voltage' => 12,
                'type' => 'litio',
                'price' => 9000000,
            ],
            [
                'brand' => 'Victron Energy',
                'model' => 'SuperPack 200Ah',
                'capacity' => 200,
                'voltage' => 12,
                'type' => 'litio',
                'price' => 13500000,
            ],
            [
                'brand' => 'Rolls',
                'model' => 'Surrette S6-L16',
                'capacity' => 415,
                'voltage' => 6,
                'type' => 'plomo_acido',
                'price' => 3000000,
            ],
            [
                'brand' => 'Trojan',
                'model' => 'T-105',
                'capacity' => 225,
                'voltage' => 6,
                'type' => 'plomo_acido',
                'price' => 1000000,
            ],
            [
                'brand' => 'Century',
                'model' => 'GF-120',
                'capacity' => 120,
                'voltage' => 12,
                'type' => 'gel',
                'price' => 750000,
            ],
            [
                'brand' => 'Fullriver',
                'model' => 'DC150-12',
                'capacity' => 150,
                'voltage' => 12,
                'type' => 'gel',
                'price' => 1200000,
            ],
            [
                'brand' => 'Discover',
                'model' => 'AES 200Ah',
                'capacity' => 200,
                'voltage' => 12,
                'type' => 'gel',
                'price' => 1750000,
            ],
        ];

        foreach ($batteries as $battery) {
            Battery::create($battery);
        }
    }
}
