<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $clients = [
            // === CLIENTES RESIDENCIALES ===
            [
                'name' => 'Juan Carlos Rodríguez',
                'client_type_id' => 1, // Residencial
                'document_type' => 'CC',
                'document_number' => '12345678',
                'nic' => '901234567890',
                'email' => 'juan.rodriguez@email.com',
                'phone' => '3001234567',
                'mobile' => '3001234567',
                'address' => 'Calle 123 #45-67, Bogotá',
                'department_id' => 1, // Cundinamarca
                'city_id' => 1, // Bogotá
                'monthly_consumption_kwh' => 350.50,
                'tariff_cop_kwh' => 650.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Cliente residencial interesado en paneles solares para vivienda',
                'is_active' => true,
            ],
            [
                'name' => 'María González López',
                'client_type_id' => 1, // Residencial
                'document_type' => 'CC',
                'document_number' => '87654321',
                'nic' => '908765432109',
                'email' => 'maria.gonzalez@email.com',
                'phone' => '3012345678',
                'mobile' => '3012345678',
                'address' => 'Carrera 15 #89-12, Medellín',
                'department_id' => 2, // Antioquia
                'city_id' => 2, // Medellín
                'monthly_consumption_kwh' => 280.75,
                'tariff_cop_kwh' => 620.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Casa con techo adecuado para instalación de paneles',
                'is_active' => true,
            ],
            [
                'name' => 'Carlos Martínez Silva',
                'client_type_id' => 1, // Residencial
                'document_type' => 'CC',
                'document_number' => '11223344',
                'nic' => '907654321098',
                'email' => 'carlos.martinez@email.com',
                'phone' => '3023456789',
                'mobile' => '3023456789',
                'address' => 'Avenida 6 Norte #45-23, Cali',
                'department_id' => 3, // Valle del Cauca
                'city_id' => 3, // Cali
                'monthly_consumption_kwh' => 420.30,
                'tariff_cop_kwh' => 680.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Cliente con alto consumo, excelente candidato para solar',
                'is_active' => true,
            ],

            // === CLIENTES COMERCIALES ===
            [
                'name' => 'Supermercados La Economía S.A.S.',
                'client_type_id' => 2, // Comercial
                'document_type' => 'NIT',
                'document_number' => '901234567-8',
                'nic' => '800123456789',
                'email' => 'contacto@supereconomia.com',
                'phone' => '6012345678',
                'mobile' => '3109876543',
                'address' => 'Centro Comercial Los Andes, Local 45, Bogotá',
                'department_id' => 1, // Cundinamarca
                'city_id' => 1, // Bogotá
                'monthly_consumption_kwh' => 2500.00,
                'tariff_cop_kwh' => 720.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Cadena de supermercados con múltiples locales, proyecto a gran escala',
                'is_active' => true,
            ],
            [
                'name' => 'Hotel Boutique Santa Fe',
                'client_type_id' => 2, // Comercial
                'document_type' => 'NIT',
                'document_number' => '902345678-9',
                'nic' => '801234567890',
                'email' => 'reservas@hotelsantafe.com',
                'phone' => '6045678901',
                'mobile' => '3112345678',
                'address' => 'Carrera 7 #45-23, Medellín',
                'department_id' => 2, // Antioquia
                'city_id' => 2, // Medellín
                'monthly_consumption_kwh' => 1800.50,
                'tariff_cop_kwh' => 750.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Hotel boutique interesado en reducir costos energéticos',
                'is_active' => true,
            ],
            [
                'name' => 'Centro Médico San José',
                'client_type_id' => 2, // Comercial
                'document_type' => 'NIT',
                'document_number' => '903456789-0',
                'nic' => '802345678901',
                'email' => 'admin@centromedico.com',
                'phone' => '6023456789',
                'mobile' => '3123456789',
                'address' => 'Avenida 5 Norte #23-45, Cali',
                'department_id' => 3, // Valle del Cauca
                'city_id' => 3, // Cali
                'monthly_consumption_kwh' => 3200.75,
                'tariff_cop_kwh' => 780.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Centro médico con equipos críticos que requieren energía confiable',
                'is_active' => true,
            ],

            // === CLIENTES INDUSTRIALES ===
            [
                'name' => 'Cementos del Valle S.A.',
                'client_type_id' => 3, // Industrial
                'document_type' => 'NIT',
                'document_number' => '800123456-7',
                'nic' => '803456789012',
                'email' => 'energia@cementosdelvalle.com',
                'phone' => '6024567890',
                'mobile' => '3134567890',
                'address' => 'Zona Industrial, Yumbo, Valle del Cauca',
                'department_id' => 3, // Valle del Cauca
                'city_id' => 4, // Yumbo
                'monthly_consumption_kwh' => 15000.00,
                'tariff_cop_kwh' => 650.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Productor de cemento con alto consumo industrial, candidato ideal para solar',
                'is_active' => true,
            ],
            [
                'name' => 'Textiles Antioquia Ltda.',
                'client_type_id' => 3, // Industrial
                'document_type' => 'NIT',
                'document_number' => '801234567-8',
                'nic' => '804567890123',
                'email' => 'operaciones@textilesantioquia.com',
                'phone' => '6045678901',
                'mobile' => '3145678901',
                'address' => 'Parque Industrial, Itagüí, Antioquia',
                'department_id' => 2, // Antioquia
                'city_id' => 5, // Itagüí
                'monthly_consumption_kwh' => 8500.25,
                'tariff_cop_kwh' => 680.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Fábrica textil con procesos que requieren energía constante',
                'is_active' => true,
            ],

            // === CLIENTES INSTITUCIONALES ===
            [
                'name' => 'Universidad Nacional de Colombia',
                'client_type_id' => 4, // Institucional
                'document_type' => 'NIT',
                'document_number' => '899999063-1',
                'nic' => '805678901234',
                'email' => 'sostenibilidad@unal.edu.co',
                'phone' => '6012345678',
                'mobile' => '3156789012',
                'address' => 'Carrera 45 #26-85, Bogotá',
                'department_id' => 1, // Cundinamarca
                'city_id' => 1, // Bogotá
                'monthly_consumption_kwh' => 12500.00,
                'tariff_cop_kwh' => 700.00,
                'responsible_user_id' => 1, // Admin
                'notes' => 'Universidad pública con compromiso ambiental, proyecto institucional',
                'is_active' => true,
            ],
        ];

        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
