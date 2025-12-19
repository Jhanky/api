<?php

namespace App\Observers;

use App\Models\Quotation;
use App\Models\Project;
use App\Models\ProjectState;

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
        // Este observer está desactivado para evitar conflictos con el controller
        // La creación de proyectos se maneja desde QuotationController::updateStatus
        return;

        // Código original comentado por si se necesita en el futuro:
        /*
        // Verificar si el estado cambió a "Contratada" (ID 5)
        if ($quotation->wasChanged('status_id') && $quotation->status_id == 5) {
            \Log::info('Observer detectó cambio a Contratada. Cotización ID: ' . $quotation->id);
            $this->createProjectFromQuotation($quotation);
        }
        */
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
     * NOTA: Este método está obsoleto. Se maneja desde el controller.
     */
    private function createProjectFromQuotation(Quotation $quotation): void
    {
        try {
            \Log::info('Iniciando creación de proyecto para cotización: ' . $quotation->id);

            // Verificar que no exista ya un proyecto para esta cotización
            if (Project::where('quotation_id', $quotation->id)->exists()) {
                \Log::warning('Ya existe un proyecto para la cotización #' . $quotation->id);
                return;
            }

            // Obtener el estado inicial para el proyecto (Borrador - ID 1)
            $initialState = ProjectState::find(1); // Estado "Borrador"

            if (!$initialState) {
                \Log::error('Estado inicial de proyecto no encontrado (ID 1)');
                return;
            }

            \Log::info('Estado del proyecto: ' . $initialState->name);

            // Crear el proyecto con los campos correctos
            $project = Project::create([
                'quotation_id' => $quotation->id,
                'client_id' => $quotation->client_id,
                'department_id' => $quotation->client->department_id,
                'city_id' => $quotation->client->city_id,
                'current_state_id' => $initialState->id,
                'name' => $quotation->project_name,
                'contracted_value_cop' => $quotation->total_value,
                'start_date' => now(),
                'project_manager_id' => $quotation->user_id,
                'notes' => 'Proyecto creado automáticamente al contratar la cotización #' . $quotation->code . '. Pendiente visita técnica para geolocalización.',
                'is_active' => true,
            ]);

            \Log::info('Proyecto creado exitosamente con ID: ' . $project->id);

        } catch (\Exception $e) {
            \Log::error('Error al crear proyecto: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
        }
    }
}
