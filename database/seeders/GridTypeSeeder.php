<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GridType;

class GridTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Trifásica 220V',
                'code' => 'TRI220',
                'description' => 'Sistema eléctrico trifásico a 220 Voltios',
            ],
            [
                'name' => 'Monofásica',
                'code' => 'MONO',
                'description' => 'Sistema eléctrico monofásico estándar',
            ],
            [
                'name' => 'Trifásica 440V',
                'code' => 'TRI440',
                'description' => 'Sistema eléctrico trifásico a 440 Voltios',
            ],
            [
                'name' => 'Bifásica 220V',
                'code' => 'BI220',
                'description' => 'Sistema eléctrico bifásico a 220 Voltios',
            ],
        ];

        foreach ($types as $type) {
            GridType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
