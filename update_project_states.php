<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\ProjectState;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Actualizando estados de proyecto...\n";

$states = [
    [
        'id' => 1,
        'name' => 'Borrador',
        'code' => 'BORRADOR',
        'slug' => 'borrador',
        'description' => 'Proyecto en estado inicial, recopilando información básica',
        'color' => '#94a3b8',
        'icon' => 'FileText',
        'display_order' => 1,
        'phase' => 'commercial',
        'estimated_duration' => 3,
        'is_final' => false,
        'requires_approval' => false,
        'is_active' => true,
    ],
            [
                'id' => 2,
                'name' => 'Solicitud de Factibilidad',
                'code' => 'SOL_FACTIBILIDAD',
                'slug' => 'solicitud_factibilidad',
                'description' => 'Solicitud de estudio de factibilidad técnica presentada',
                'color' => '#60a5fa',
                'icon' => 'Send',
                'display_order' => 2,
                'phase' => 'commercial',
                'estimated_duration' => 7,
                'is_final' => false,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'id' => 3,
                'name' => 'Factibilidad Aprobada',
                'code' => 'FACT_APROBADA',
                'slug' => 'factibilidad_aprobada',
                'description' => 'Estudio de factibilidad aprobado, proyecto viable técnicamente',
                'color' => '#84cc16',
                'icon' => 'CheckCircle',
                'display_order' => 3,
                'phase' => 'technical',
                'estimated_duration' => 1,
                'is_final' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
    [
        'id' => 4,
        'name' => 'Diseño Eléctrico',
        'code' => 'DISEÑO_ELECTRICO',
        'slug' => 'diseno_electrico',
        'description' => 'Desarrollo del diseño eléctrico e ingeniería del sistema',
        'color' => '#f59e0b',
        'icon' => 'Settings',
        'display_order' => 4,
        'phase' => 'technical',
        'estimated_duration' => 14,
        'is_final' => false,
        'requires_approval' => false,
        'is_active' => true,
    ],
    [
        'id' => 5,
        'name' => 'Diseño Conforme',
        'code' => 'DISEÑO_CONFORME',
        'slug' => 'diseno_conforme',
        'description' => 'Diseño técnico aprobado y conforme a normativas',
        'color' => '#8b5cf6',
        'icon' => 'Award',
        'display_order' => 5,
        'phase' => 'technical',
        'estimated_duration' => 2,
        'is_final' => false,
        'requires_approval' => true,
        'is_active' => true,
    ],
    [
        'id' => 6,
        'name' => 'Construcción',
        'code' => 'CONSTRUCCION',
        'slug' => 'construccion',
        'description' => 'Ejecución de obra civil e instalación de equipos',
        'color' => '#3b82f6',
        'icon' => 'Hammer',
        'display_order' => 6,
        'phase' => 'technical',
        'estimated_duration' => 21,
        'is_final' => false,
        'requires_approval' => false,
        'is_active' => true,
    ],
    [
        'id' => 7,
        'name' => 'Obra Terminada',
        'code' => 'OBRA_TERMINADA',
        'slug' => 'obra_terminada',
        'description' => 'Instalación física completada, lista para pruebas',
        'color' => '#06b6d4',
        'icon' => 'CheckSquare',
        'display_order' => 7,
        'phase' => 'technical',
        'estimated_duration' => 1,
        'is_final' => false,
        'requires_approval' => false,
        'is_active' => true,
    ],
    [
        'id' => 8,
        'name' => 'Conexión Aprobada',
        'code' => 'CONEXION_APROBADA',
        'slug' => 'conexion_aprobada',
        'description' => 'Aprobación regulatoria para conexión a red eléctrica',
        'color' => '#14b8a6',
        'icon' => 'Zap',
        'display_order' => 8,
        'phase' => 'legal',
        'estimated_duration' => 5,
        'is_final' => false,
        'requires_approval' => true,
        'is_active' => true,
    ],
    [
        'id' => 9,
        'name' => 'Energizado',
        'code' => 'ENERGIZADO',
        'slug' => 'energizado',
        'description' => 'Sistema conectado a red y energizado',
        'color' => '#22c55e',
        'icon' => 'Power',
        'display_order' => 9,
        'phase' => 'completed',
        'estimated_duration' => 1,
        'is_final' => false,
        'requires_approval' => false,
        'is_active' => true,
    ],
    [
        'id' => 10,
        'name' => 'Operación en Red',
        'code' => 'OPERACION_RED',
        'slug' => 'operacion_red',
        'description' => 'Proyecto en operación comercial generando energía',
        'color' => '#16a34a',
        'icon' => 'Activity',
        'display_order' => 10,
        'phase' => 'completed',
        'estimated_duration' => null,
        'is_final' => true,
        'requires_approval' => false,
        'is_active' => true,
    ],
];

foreach ($states as $state) {
    ProjectState::updateOrCreate(
        ['id' => $state['id']],
        $state
    );
    echo "Actualizado estado ID {$state['id']}: {$state['name']}\n";
}

// Eliminar estados antiguos que ya no se necesitan (11, 12, 13)
ProjectState::where('id', '>', 10)->delete();
echo "Eliminados estados antiguos (IDs 11-13)\n";

echo "¡Actualización completada!\n";
