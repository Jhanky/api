<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            [
                'provider_name' => 'Energía Solar S.A.S',
                'provider_tax_id' => '900123456-1'
            ],
            [
                'provider_name' => 'Paneles del Norte Ltda',
                'provider_tax_id' => '800987654-3'
            ],
            [
                'provider_name' => 'Inversores Eléctricos S.A',
                'provider_tax_id' => '890123456-7'
            ],
            [
                'provider_name' => 'Baterías Renovables Colombia',
                'provider_tax_id' => '901234567-8'
            ],
            [
                'provider_name' => 'Instalaciones Verdes S.A.S',
                'provider_tax_id' => '912345678-9'
            ],
            [
                'provider_name' => 'Materiales Eléctricos Pro',
                'provider_tax_id' => '923456789-0'
            ],
            [
                'provider_name' => 'Servicios Técnicos Solares',
                'provider_tax_id' => '934567890-1'
            ],
            [
                'provider_name' => 'Distribuidora de Energía',
                'provider_tax_id' => '945678901-2'
            ]
        ];

        foreach ($providers as $provider) {
            \App\Models\Provider::create($provider);
        }
    }
}
