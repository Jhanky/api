<?php

namespace App\Jobs;

use App\Models\WarehouseStock;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $warehouseStock;

    /**
     * Create a new job instance.
     */
    public function __construct(WarehouseStock $warehouseStock)
    {
        $this->warehouseStock = $warehouseStock;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Cargar relaciones
        $this->warehouseStock->load(['material', 'warehouse']);

        $material = $this->warehouseStock->material;
        $warehouse = $this->warehouseStock->warehouse;

        // Obtener gerentes y administradores
        $managers = User::whereHas('role', function ($query) {
            $query->whereIn('name', ['Gerente', 'Administrador', 'Admin']);
        })->get();

        // Log de la alerta
        Log::warning('Stock bajo detectado', [
            'material' => $material->description,
            'warehouse' => $warehouse->name,
            'quantity' => $this->warehouseStock->quantity,
            'min_stock' => 0, // Valor por defecto - ya no se usa
            'deficit' => 0 // Valor por defecto - ya no se usa
        ]);

        // Aquí se pueden agregar notificaciones por email, Slack, etc.
        // Por ahora solo registramos en el log
        
        // Ejemplo de notificación (descomentar cuando se implemente):
        // foreach ($managers as $manager) {
        //     $manager->notify(new LowStockNotification($this->warehouseStock));
        // }
    }
}
