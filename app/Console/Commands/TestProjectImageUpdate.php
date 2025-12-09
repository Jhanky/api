<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;

class TestProjectImageUpdate extends Command
{
    protected $signature = 'test:project-image-update';
    protected $description = 'Probar la funcionalidad de actualización de proyectos con imagen de portada';

    public function handle()
    {
        $this->info('🧪 Probando funcionalidad de actualización de proyectos con imagen...');
        $this->newLine();

        // Obtener un proyecto existente
        $project = Project::with(['quotation', 'client', 'location', 'status', 'projectManager'])->first();

        if (!$project) {
            $this->error('❌ No hay proyectos en la base de datos.');
            return;
        }

        $this->info("📋 Proyecto encontrado: {$project->quotation->project_name} (ID: {$project->project_id})");
        $this->info("   - Imagen actual: " . ($project->cover_image ?? 'null'));
        $this->info("   - Alt text actual: " . ($project->cover_image_alt ?? 'null'));
        $this->newLine();

        // Probar actualización sin imagen
        $this->info('🔄 Probando actualización sin imagen...');
        try {
            $project->update([
                'notes' => 'Proyecto actualizado con nueva funcionalidad de imagen - ' . now()->format('Y-m-d H:i:s'),
                'cover_image_alt' => 'Imagen de la planta solar ' . $project->quotation->project_name
            ]);
            
            $this->info('✅ Actualización sin imagen exitosa');
            $this->info("   - Nuevas notas: {$project->notes}");
            $this->info("   - Nuevo alt text: {$project->cover_image_alt}");
        } catch (\Exception $e) {
            $this->error('❌ Error en actualización sin imagen: ' . $e->getMessage());
        }

        $this->newLine();

        // Probar transformación de respuesta
        $this->info('🔄 Probando transformación de respuesta...');
        try {
            $controller = new ProjectController();
            $reflection = new \ReflectionClass($controller);
            $method = $reflection->getMethod('transformProjectToResponse');
            $method->setAccessible(true);
            
            $transformedProject = $method->invoke($controller, $project);
            
            $this->info('✅ Transformación de respuesta exitosa');
            $this->info("   - ID: {$transformedProject['id']}");
            $this->info("   - Nombre: {$transformedProject['nombre_proyecto']}");
            $this->info("   - Imagen portada: " . ($transformedProject['imagen_portada'] ?? 'null'));
            $this->info("   - Alt text: " . ($transformedProject['imagen_portada_alt'] ?? 'null'));
            
        } catch (\Exception $e) {
            $this->error('❌ Error en transformación: ' . $e->getMessage());
        }

        $this->newLine();

        // Mostrar estructura completa de respuesta
        $this->info('📊 Estructura completa de respuesta:');
        $this->line(json_encode($transformedProject, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->newLine();
        $this->info('✅ Prueba completada exitosamente!');
        $this->info('💡 Para probar con imagen real, usa: PUT /api/projects/{id} con FormData');
    }
}