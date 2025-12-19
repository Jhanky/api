<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\ProjectState;

class ProjectStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Verificar si ya existen estados para evitar duplicados
        if (ProjectState::count() > 0) {
            return;
        }

        $states = [
            [
                'name' => 'Borrador',
                'slug' => 'draft',
                'description' => 'Proyecto creado, pendiente de iniciar',
                'color' => '#94a3b8',
                'icon' => 'FileText',
                'display_order' => 1,
                'phase' => 'commercial',
                'is_final' => false,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'En Planeación',
                'slug' => 'planning',
                'description' => 'Proyecto en fase de planeación técnica',
                'color' => '#3b82f6',
                'icon' => 'ClipboardList',
                'display_order' => 2,
                'phase' => 'technical',
                'is_final' => false,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'En Ejecución',
                'slug' => 'in_progress',
                'description' => 'Proyecto en ejecución/instalación',
                'color' => '#f59e0b',
                'icon' => 'Wrench',
                'display_order' => 3,
                'phase' => 'technical',
                'is_final' => false,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Pendiente Legalización',
                'slug' => 'pending_legalization',
                'description' => 'Proyecto pendiente de trámites legales',
                'color' => '#8b5cf6',
                'icon' => 'Scale',
                'display_order' => 4,
                'phase' => 'legal',
                'is_final' => false,
                'requires_approval' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Completado',
                'slug' => 'completed',
                'description' => 'Proyecto finalizado exitosamente',
                'color' => '#16a34a',
                'icon' => 'CheckCircle',
                'display_order' => 5,
                'phase' => 'completed',
                'is_final' => true,
                'requires_approval' => false,
                'is_active' => true,
            ],
            [
                'name' => 'Cancelado',
                'slug' => 'cancelled',
                'description' => 'Proyecto cancelado',
                'color' => '#dc2626',
                'icon' => 'XCircle',
                'display_order' => 6,
                'phase' => 'cancelled',
                'is_final' => true,
                'requires_approval' => true,
                'is_active' => true,
            ],
        ];

        foreach ($states as $state) {
            ProjectState::create($state);
        }
    }
}
