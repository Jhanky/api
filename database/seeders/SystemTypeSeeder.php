<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SystemType;

class SystemTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'On-grid',
                'code' => 'ONGRID',
                'description' => 'Sistema conectado a la red eléctrica sin baterías',
            ],
            [
                'name' => 'Híbrido',
                'code' => 'HYBRID',
                'description' => 'Sistema con paneles solares y respaldo de baterías',
            ],
            [
                'name' => 'Off-grid',
                'code' => 'OFFGRID',
                'description' => 'Sistema autónomo no conectado a la red eléctrica',
            ],
        ];

        foreach ($types as $type) {
            SystemType::updateOrCreate(['code' => $type['code']], $type);
        }
    }
}
