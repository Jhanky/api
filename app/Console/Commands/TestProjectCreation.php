<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Quotation;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Http\Controllers\Api\QuotationController;

class TestProjectCreation extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:project-creation {quotation_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prueba la creación automática de proyectos al cambiar estado de cotización';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $quotationId = $this->argument('quotation_id');
        
        $this->info("Probando creación de proyecto para cotización #{$quotationId}");
        
        // Buscar la cotización
        $quotation = Quotation::with(['status', 'client'])->find($quotationId);
        if (!$quotation) {
            $this->error("Cotización #{$quotationId} no encontrada");
            return 1;
        }
        
        $this->info("Cotización encontrada: {$quotation->project_name}");
        $this->info("Estado actual: {$quotation->status->name} (ID: {$quotation->status_id})");
        
        // Verificar si ya existe un proyecto
        $existingProject = Project::where('quotation_id', $quotationId)->first();
        if ($existingProject) {
            $this->warn("Ya existe un proyecto para esta cotización: ID {$existingProject->project_id}");
            return 0;
        }
        
        // Crear una request simulada para el controlador
        $request = new \Illuminate\Http\Request();
        $request->merge(['status_id' => 5]); // Estado "Contratada"
        
        // Usar el controlador para cambiar el estado
        $controller = new QuotationController();
        $response = $controller->updateStatus($request, $quotationId);
        
        $this->info("Estado cambiado usando el controlador");
        
        // Verificar si se creó el proyecto
        $project = Project::where('quotation_id', $quotationId)->first();
        if ($project) {
            $this->info("✅ Proyecto creado exitosamente!");
            $this->info("   - ID del proyecto: {$project->project_id}");
            $this->info("   - Nombre: {$project->project_name}");
            $this->info("   - Estado: {$project->status->name} (ID: {$project->status_id})");
            $this->info("   - Fecha de inicio: {$project->start_date}");
            
            // Mostrar respuesta del controlador
            $responseData = json_decode($response->getContent(), true);
            if (isset($responseData['data']['project_created'])) {
                $this->info("   - Respuesta del controlador: Proyecto creado automáticamente");
            }
        } else {
            $this->error("❌ No se creó el proyecto automáticamente");
            
            // Mostrar respuesta del controlador para debug
            $responseData = json_decode($response->getContent(), true);
            $this->info("Respuesta del controlador: " . json_encode($responseData, JSON_PRETTY_PRINT));
        }
        
        // Restaurar estado original
        $quotation->update(['status_id' => $quotation->status_id]);
        $this->info("Estado restaurado a: {$quotation->status->name}");
        
        return 0;
    }
}
