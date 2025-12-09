<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Models\Project;
use App\Models\ProjectStatus;
use App\Models\Location;

class QuotationObserver
{
    /**
     * Handle the Quotation "created" event.
     */
    public function created(Quotation $quotation): void
    {
        //
    }

    /**
     * Handle the Quotation "updated" event.
     */
    public function updated(Quotation $quotation): void
    {
        // Verificar si el estado cambió a "Contratada" (ID 5)
        if ($quotation->wasChanged('status_id') && $quotation->status_id == 5) {
            \Log::info('Observer detectó cambio a Contratada. Cotización ID: ' . $quotation->quotation_id);
            $this->createProjectFromQuotation($quotation);
        }
    }

    /**
     * Handle the Quotation "deleted" event.
     */
    public function deleted(Quotation $quotation): void
    {
        //
    }

    /**
     * Handle the Quotation "restored" event.
     */
    public function restored(Quotation $quotation): void
    {
        //
    }

    /**
     * Handle the Quotation "force deleted" event.
     */
    public function forceDeleted(Quotation $quotation): void
    {
        //
    }

    /**
     * Crear proyecto automáticamente cuando la cotización se convierte en contratada
     */
    private function createProjectFromQuotation(Quotation $quotation): void
    {
        try {
            \Log::info('Iniciando creación de proyecto para cotización: ' . $quotation->quotation_id);
            
            // Obtener el estado "Iniciado" para el proyecto
            $initialStatus = ProjectStatus::where('name', 'Iniciado')->first();
            
            if (!$initialStatus) {
                // Si no existe el estado, usar el primero disponible
                $initialStatus = ProjectStatus::first();
            }
            
            \Log::info('Estado del proyecto: ' . ($initialStatus ? $initialStatus->name : 'No encontrado'));

            // Obtener la ubicación del cliente
            $location = $quotation->client->location ?? Location::first();
            
            \Log::info('Ubicación encontrada: ' . ($location ? $location->location_id : 'No encontrada'));

            // Crear el proyecto
            $project = Project::create([
                'quotation_id' => $quotation->quotation_id,
                'client_id' => $quotation->client_id,
                'location_id' => $location ? $location->location_id : null,
                'status_id' => $initialStatus->status_id,
                'project_name' => $quotation->project_name,
                'start_date' => now(),
                'project_manager_id' => $quotation->user_id, // El usuario que creó la cotización será el gerente inicial
                'notes' => 'Proyecto creado automáticamente al contratar la cotización #' . $quotation->quotation_id . '. Pendiente visita técnica para geolocalización.',
                // Los campos de georreferenciación se actualizarán después mediante visita técnica
            ]);
            
            \Log::info('Proyecto creado exitosamente con ID: ' . $project->project_id);
            
        } catch (\Exception $e) {
            \Log::error('Error al crear proyecto: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
