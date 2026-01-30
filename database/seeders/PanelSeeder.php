<?php

namespace Database\Seeders;

use App\Models\Panel;
use Illuminate\Database\Seeder;

class PanelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $panels = [
            [
                'brand' => 'JA Solar',
                'model' => 'JAM72S30',
                'power' => 550,
                'price' => 680000,
            ],
            [
                'brand' => 'Canadian Solar',
                'model' => 'HiKu6',
                'power' => 545,
                'price' => 670000,
            ],
            [
                'brand' => 'Trina Solar',
                'model' => 'Vertex S+',
                'power' => 560,
                'price' => 695000,
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'Tiger Pro',
                'power' => 535,
                'price' => 650000,
            ],
            [
                'brand' => 'LONGi Solar',
                'model' => 'Hi-MO 5',
                'power' => 555,
                'price' => 685000,
            ],
            [
                'brand' => 'Hanwha Q CELLS',
                'model' => 'Q.PEAK DUO-G9+',
                'power' => 540,
                'price' => 720000,
            ],
            [
                'brand' => 'Sunrun',
                'model' => 'SR-M550',
                'power' => 550,
                'price' => 700000,
            ],
            [
                'brand' => 'Panasonic',
                'model' => 'HIT N330',
                'power' => 330,
                'price' => 550000,
            ],
            [
                'brand' => 'LG Electronics',
                'model' => 'NeON R',
                'power' => 380,
                'price' => 620000,
            ],
            [
                'brand' => 'SunPower',
                'model' => 'Maxeon 3',
                'power' => 400,
                'price' => 850000,
            ],
        ];

        foreach ($panels as $panel) {
            Panel::create($panel);
        }
    }
}
