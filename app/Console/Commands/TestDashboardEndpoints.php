<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Api\DashboardController;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Quotation;
use App\Models\Client;
use App\Models\Location;
use App\Models\User;

class TestDashboardEndpoints extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dashboard:test-endpoints';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar los endpoints del dashboard creando datos de ejemplo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Iniciando prueba de endpoints del dashboard...');
        
        // Crear datos de ejemplo si no existen
        $this->createSampleData();
        
        // Probar el controlador
        $this->testDashboardController();
        
        $this->info('✅ Prueba completada exitosamente!');
    }
    
    private function createSampleData()
    {
        $this->info('📊 Creando datos de ejemplo...');
        
        // Crear estados de proyecto si no existen
        $activeStatus = ProjectStatus::firstOrCreate(
            ['name' => 'Activo'],
            [
                'description' => 'Proyecto en funcionamiento',
                'color' => '#28a745',
                'is_active' => true
            ]
        );
        
        $completedStatus = ProjectStatus::firstOrCreate(
            ['name' => 'Completado'],
            [
                'description' => 'Proyecto completado exitosamente',
                'color' => '#17a2b8',
                'is_active' => false
            ]
        );
        
        // Crear ubicaciones si no existen
        $cartagenaLocation = Location::firstOrCreate(
            ['municipality' => 'Cartagena', 'department' => 'Bolívar'],
            [
                'radiation' => 5.5
            ]
        );
        
        $barranquillaLocation = Location::firstOrCreate(
            ['municipality' => 'Barranquilla', 'department' => 'Atlántico'],
            [
                'radiation' => 5.2
            ]
        );
        
        // Crear clientes si no existen
        $client1 = Client::firstOrCreate(
            ['nic' => '900123456-1'],
            [
                'client_type' => 'Empresa',
                'name' => 'Empresa ABC S.A.S',
                'department' => 'Bolívar',
                'city' => 'Cartagena',
                'address' => 'Calle 123 #45-67, Cartagena',
                'monthly_consumption_kwh' => 500.00,
                'energy_rate' => 0.8500,
                'network_type' => 'Monofásica',
                'user_id' => 1,
                'is_active' => true
            ]
        );
        
        $client2 = Client::firstOrCreate(
            ['nic' => '900987654-2'],
            [
                'client_type' => 'Empresa',
                'name' => 'Comercial XYZ Ltda',
                'department' => 'Atlántico',
                'city' => 'Barranquilla',
                'address' => 'Carrera 45 #78-90, Barranquilla',
                'monthly_consumption_kwh' => 750.00,
                'energy_rate' => 0.9200,
                'network_type' => 'Trifásica',
                'user_id' => 1,
                'is_active' => true
            ]
        );
        
        // Crear usuarios si no existen
        $projectManager1 = User::firstOrCreate(
            ['email' => 'juan.perez@energy4cero.com'],
            [
                'name' => 'Juan Pérez',
                'username' => 'juan.perez',
                'password' => bcrypt('password123')
            ]
        );
        
        $projectManager2 = User::firstOrCreate(
            ['email' => 'maria.garcia@energy4cero.com'],
            [
                'name' => 'María García',
                'username' => 'maria.garcia',
                'password' => bcrypt('password123')
            ]
        );
        
        // Crear cotizaciones si no existen
        $quotation1 = Quotation::firstOrCreate(
            ['project_name' => 'Planta Solar Cartagena'],
            [
                'client_id' => $client1->client_id,
                'user_id' => $projectManager1->id,
                'system_type' => 'Sistema On-Grid',
                'power_kwp' => 8.0,
                'panel_count' => 20,
                'requires_financing' => false,
                'profit_percentage' => 0.15,
                'iva_profit_percentage' => 0.19,
                'commercial_management_percentage' => 0.05,
                'administration_percentage' => 0.03,
                'contingency_percentage' => 0.02,
                'withholding_percentage' => 0.035,
                'subtotal' => 15000000,
                'total_value' => 18000000,
                'status_id' => 1
            ]
        );
        
        $quotation2 = Quotation::firstOrCreate(
            ['project_name' => 'Planta Solar Barranquilla'],
            [
                'client_id' => $client2->client_id,
                'user_id' => $projectManager2->id,
                'system_type' => 'Sistema On-Grid',
                'power_kwp' => 6.5,
                'panel_count' => 16,
                'requires_financing' => true,
                'profit_percentage' => 0.15,
                'iva_profit_percentage' => 0.19,
                'commercial_management_percentage' => 0.05,
                'administration_percentage' => 0.03,
                'contingency_percentage' => 0.02,
                'withholding_percentage' => 0.035,
                'subtotal' => 12000000,
                'total_value' => 14500000,
                'status_id' => 1
            ]
        );
        
        // Crear proyectos si no existen
        Project::firstOrCreate(
            ['project_name' => 'Planta Solar Cartagena'],
            [
                'quotation_id' => $quotation1->quotation_id,
                'client_id' => $client1->client_id,
                'location_id' => $cartagenaLocation->location_id,
                'status_id' => $activeStatus->status_id,
                'start_date' => now()->subDays(30),
                'estimated_end_date' => now()->addDays(30),
                'project_manager_id' => $projectManager1->id,
                'budget' => 18000000,
                'notes' => 'Proyecto de instalación solar residencial',
                'latitude' => 10.3932,
                'longitude' => -75.4792,
                'installation_address' => 'Calle 123 #45-67, Cartagena, Bolívar'
            ]
        );
        
        Project::firstOrCreate(
            ['project_name' => 'Planta Solar Barranquilla'],
            [
                'quotation_id' => $quotation2->quotation_id,
                'client_id' => $client2->client_id,
                'location_id' => $barranquillaLocation->location_id,
                'status_id' => $activeStatus->status_id,
                'start_date' => now()->subDays(15),
                'estimated_end_date' => now()->addDays(45),
                'project_manager_id' => $projectManager2->id,
                'budget' => 14500000,
                'notes' => 'Proyecto de instalación solar comercial',
                'latitude' => 10.9685,
                'longitude' => -74.7813,
                'installation_address' => 'Carrera 45 #78-90, Barranquilla, Atlántico'
            ]
        );
        
        $this->info('✅ Datos de ejemplo creados exitosamente');
    }
    
    private function testDashboardController()
    {
        $this->info('🧪 Probando controlador del dashboard...');
        
        $controller = new DashboardController();
        
        // Probar obtener todos los proyectos
        $this->info('📋 Probando endpoint: GET /api/dashboard/projects');
        $projectsResponse = $controller->getProjects();
        $projectsData = json_decode($projectsResponse->getContent(), true);
        
        if ($projectsData['success']) {
            $this->info("✅ Proyectos obtenidos: " . count($projectsData['data']) . " proyectos");
            foreach ($projectsData['data'] as $project) {
                $this->line("   - {$project['nombre']} ({$project['ubicacion']}) - {$project['estado']}");
            }
        } else {
            $this->error("❌ Error al obtener proyectos: " . $projectsData['message']);
        }
        
        // Probar obtener proyectos activos
        $this->info('📋 Probando endpoint: GET /api/dashboard/projects/active');
        $activeProjectsResponse = $controller->getActiveProjects();
        $activeProjectsData = json_decode($activeProjectsResponse->getContent(), true);
        
        if ($activeProjectsData['success']) {
            $this->info("✅ Proyectos activos obtenidos: " . count($activeProjectsData['data']) . " proyectos");
            foreach ($activeProjectsData['data'] as $project) {
                $this->line("   - {$project['nombre']} ({$project['ubicacion']}) - {$project['estado']}");
            }
        } else {
            $this->error("❌ Error al obtener proyectos activos: " . $activeProjectsData['message']);
        }
        
        // Probar obtener estadísticas
        $this->info('📊 Probando endpoint: GET /api/dashboard/stats');
        $statsResponse = $controller->getDashboardStats();
        $statsData = json_decode($statsResponse->getContent(), true);
        
        if ($statsData['success']) {
            $this->info("✅ Estadísticas obtenidas:");
            $this->line("   - Total proyectos: " . $statsData['data']['total_projects']);
            $this->line("   - Proyectos activos: " . $statsData['data']['active_projects']);
            $this->line("   - Proyectos completados: " . $statsData['data']['completed_projects']);
            $this->line("   - Capacidad total: " . $statsData['data']['total_capacity_kwp'] . " kWp");
            $this->line("   - Capacidad activa: " . $statsData['data']['active_capacity_kwp'] . " kWp");
            $this->line("   - Eficiencia promedio: " . $statsData['data']['efficiency_average'] . "%");
        } else {
            $this->error("❌ Error al obtener estadísticas: " . $statsData['message']);
        }
    }
}
