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
                'type' => 'monocristalino',
                'price' => 180000,
            ],
            [
                'brand' => 'Canadian Solar',
                'model' => 'HiKu6',
                'power' => 545,
                'type' => 'monocristalino',
                'price' => 175000,
            ],
            [
                'brand' => 'Trina Solar',
                'model' => 'Vertex S+',
                'power' => 560,
                'type' => 'bifacial',
                'price' => 190000,
            ],
            [
                'brand' => 'Jinko Solar',
                'model' => 'Tiger Pro',
                'power' => 535,
                'type' => 'monocristalino',
                'price' => 165000,
            ],
            [
                'brand' => 'LONGi Solar',
                'model' => 'Hi-MO 5',
                'power' => 555,
                'type' => 'monocristalino',
                'price' => 185000,
            ],
            [
                'brand' => 'Hanwha Q CELLS',
                'model' => 'Q.PEAK DUO-G9+',
                'power' => 540,
                'type' => 'monocristalino',
                'price' => 170000,
            ],
            [
                'brand' => 'Sunrun',
                'model' => 'SR-M550',
                'power' => 550,
                'type' => 'monocristalino',
                'price' => 178000,
            ],
            [
                'brand' => 'Panasonic',
                'model' => 'HIT N330',
                'power' => 330,
                'type' => 'heterojunción',
                'price' => 220000,
            ],
            [
                'brand' => 'LG Electronics',
                'model' => 'NeON R',
                'power' => 380,
                'type' => 'monocristalino',
                'price' => 195000,
            ],
            [
                'brand' => 'SunPower',
                'model' => 'Maxeon 3',
                'power' => 400,
                'type' => 'monocristalino',
                'price' => 250000,
            ],
        ];

        foreach ($panels as $panel) {
            Panel::create($panel);
        }
    }
}
