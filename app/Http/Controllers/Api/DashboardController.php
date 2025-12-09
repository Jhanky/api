<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener todos los proyectos para la página de inicio
     * 
     * @return JsonResponse
     */
    public function getProjects(): JsonResponse
    {
        try {
            $projects = Project::with([
                'quotation',
                'client',
                'location',
                'status',
                'projectManager'
            ])->get();

            $transformedProjects = $projects->map(function ($project) {
                return $this->transformProjectForDashboard($project);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProjects,
                'message' => 'Proyectos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los proyectos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener proyectos activos para la página de inicio
     * 
     * @return JsonResponse
     */
    public function getActiveProjects(): JsonResponse
    {
        try {
            $projects = Project::with([
                'quotation',
                'client',
                'location',
                'status',
                'projectManager'
            ])->whereHas('status', function ($query) {
                $query->where('is_active', true);
            })->get();

            $transformedProjects = $projects->map(function ($project) {
                return $this->transformProjectForDashboard($project);
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProjects,
                'message' => 'Proyectos activos obtenidos exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los proyectos activos: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas generales del dashboard
     * 
     * @return JsonResponse
     */
    public function getDashboardStats(): JsonResponse
    {
        try {
            $totalProjects = Project::count();
            $activeProjects = Project::whereHas('status', function ($query) {
                $query->where('is_active', true);
            })->count();
            
            $completedProjects = Project::whereHas('status', function ($query) {
                $query->where('name', 'Completado');
            })->count();

            $totalCapacity = Project::whereHas('quotation')
                ->with('quotation')
                ->get()
                ->sum(function ($project) {
                    return $project->quotation->power_kwp ?? 0;
                });

            $activeCapacity = Project::whereHas('status', function ($query) {
                $query->where('is_active', true);
            })->whereHas('quotation')
                ->with('quotation')
                ->get()
                ->sum(function ($project) {
                    return $project->quotation->power_kwp ?? 0;
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'total_projects' => $totalProjects,
                    'active_projects' => $activeProjects,
                    'completed_projects' => $completedProjects,
                    'total_capacity_kwp' => round($totalCapacity, 2),
                    'active_capacity_kwp' => round($activeCapacity, 2),
                    'efficiency_average' => $this->calculateAverageEfficiency(),
                    'last_updated' => Carbon::now()->toISOString()
                ],
                'message' => 'Estadísticas obtenidas exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transformar un proyecto para el formato del dashboard
     * 
     * @param Project $project
     * @return array
     */
    private function transformProjectForDashboard(Project $project): array
    {
        // Obtener información de la ubicación
        $location = $project->location;
        $ubicacion = $location ? 
            ($location->municipality . ', ' . $location->department) : 
            'Ubicación no especificada';

        // Obtener coordenadas
        $coordenadas = [];
        if ($project->latitude && $project->longitude) {
            $coordenadas = [(float)$project->latitude, (float)$project->longitude];
        }

        // Obtener capacidad del sistema desde la cotización
        $capacidad = $project->quotation->power_kwp ?? 0;

        // Calcular datos de monitoreo usando fórmulas reales
        $potenciaActual = $this->simulateCurrentPower($capacidad);
        $generacionHoy = $this->calculateTodayGeneration($capacidad);
        $eficiencia = $this->calculateEfficiency();

        // Determinar estado basado en el estado del proyecto
        $estado = $this->mapProjectStatus($project->status->name ?? 'Desconocido');

        // Obtener información del cliente
        $cliente = $project->client;
        $clienteInfo = [
            'id' => $cliente->client_id ?? null,
            'nombre' => $cliente->name ?? 'Cliente no especificado',
            'tipo' => $cliente->client_type ?? null,
            'nic' => $cliente->nic ?? null,
            'ubicacion' => $cliente ? 
                ($cliente->city . ', ' . $cliente->department) : 
                'Ubicación no especificada',
            'direccion' => $cliente->address ?? null,
            'consumo_mensual_kwh' => $cliente->monthly_consumption_kwh ?? null,
            'tarifa_energia' => $cliente->energy_rate ?? null,
            'tipo_red' => $cliente->network_type ?? null
        ];

        return [
            'id' => $project->project_id,
            'nombre' => $project->quotation->project_name ?? 'Proyecto sin nombre',
            'ubicacion' => $ubicacion,
            'coordenadas' => $coordenadas,
            'capacidad' => round($capacidad, 1), // kWp
            'potenciaActual' => round($potenciaActual, 1), // kW
            'generacionHoy' => round($generacionHoy, 1), // kWh
            'estado' => $estado,
            'eficiencia' => $eficiencia,
            'ultimaActualizacion' => $project->updated_at->toISOString(),
            'fechaInicio' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
            'fechaFin' => $project->actual_end_date ? $project->actual_end_date->format('Y-m-d') : null,
            'imagenPortada' => $project->cover_image ? asset('storage/' . $project->cover_image) : null,
            'imagenPortadaAlt' => $project->cover_image_alt ?? 'Imagen de la planta solar ' . ($project->quotation->project_name ?? 'del proyecto'),
            'cliente' => $clienteInfo,
            'gerenteProyecto' => $project->projectManager->name ?? 'No asignado'
        ];
    }

    /**
     * Calcular potencia actual del sistema usando fórmula sinusoidal realista
     * 
     * Fórmula: P(t) = Ppico * sen(π * (t - tamanecer) / (tocaso - tamanecer))
     * 
     * @param float $capacidad Capacidad nominal de la planta en kW
     * @return float Potencia actual en kW
     */
    private function simulateCurrentPower(float $capacidad): float
    {
        // Parámetros de la simulación solar
        $tamanecer = 6.0;  // Hora de inicio de generación solar (6:00 AM)
        $tocaso = 18.0;    // Hora de fin de generación solar (6:00 PM)
        
        // Obtener hora actual con precisión decimal
        $horaActual = Carbon::now()->hour;
        $minutoActual = Carbon::now()->minute;
        $t = $horaActual + ($minutoActual / 60.0); // Hora decimal actual
        
        // Verificar si estamos en horario de generación solar
        if ($t < $tamanecer || $t > $tocaso) {
            return 0.0; // Sin generación fuera del horario solar
        }
        
        // Calcular potencia pico con factor de pérdidas
        $ppico = $capacidad * 0.85; // Ppico = Pnominal * 0.85
        
        // Aplicar fórmula sinusoidal: P(t) = Ppico * sen(π * (t - tamanecer) / (tocaso - tamanecer))
        $factorSinusoidal = sin(M_PI * ($t - $tamanecer) / ($tocaso - $tamanecer));
        
        // Calcular potencia actual
        $potenciaActual = $ppico * $factorSinusoidal;
        
        // Asegurar que no sea negativa (por redondeos)
        return max(0.0, $potenciaActual);
    }

    /**
     * Calcular generación diaria real usando la fórmula solar
     * 
     * @param float $capacidad
     * @return float
     */
    private function calculateTodayGeneration(float $capacidad): float
    {
        // Fórmula de generación solar diaria: G_diaria = P_pico x 4.5 x 0.85
        // P_pico: Tamaño de la planta en kW
        // 4.5: Horas pico de sol en la región (Colombia)
        // 0.85: Factor de corrección por pérdidas (15% de pérdidas)
        
        $horasPicoSol = 4.5; // Horas pico de sol promedio en Colombia
        $factorPerdidas = 0.85; // Factor de corrección por pérdidas (15% de pérdidas)
        
        return $capacidad * $horasPicoSol * $factorPerdidas;
    }

    /**
     * Calcular eficiencia del sistema basada en el factor de pérdidas
     * 
     * @return int
     */
    private function calculateEfficiency(): int
    {
        // La eficiencia se basa en el factor de pérdidas (0.85 = 85% de eficiencia)
        // Agregamos una pequeña variación realista (±2%) para simular condiciones variables
        $eficienciaBase = 85; // 85% de eficiencia base (factor de pérdidas 0.85)
        $variacion = rand(-2, 2); // Variación de ±2%
        
        return max(80, min(90, $eficienciaBase + $variacion)); // Limitar entre 80% y 90%
    }

    /**
     * Mapear estado del proyecto a estado del dashboard
     * 
     * @param string $projectStatus
     * @return string
     */
    private function mapProjectStatus(string $projectStatus): string
    {
        $statusMap = [
            'En Progreso' => 'activa',
            'Activo' => 'activa',
            'Completado' => 'completada',
            'Pausado' => 'pausada',
            'Cancelado' => 'cancelada',
            'En Planificación' => 'planificacion'
        ];

        return $statusMap[$projectStatus] ?? 'desconocida';
    }

    /**
     * Calcular eficiencia promedio de todos los proyectos activos
     * 
     * @return float
     */
    private function calculateAverageEfficiency(): float
    {
        $activeProjects = Project::whereHas('status', function ($query) {
            $query->where('is_active', true);
        })->count();

        if ($activeProjects === 0) {
            return 0;
        }

        // Eficiencia promedio basada en el factor de pérdidas real (85% base)
        // Con pequeña variación para simular condiciones variables entre proyectos
        $eficienciaBase = 85.0; // 85% de eficiencia base
        $variacion = rand(-3, 3) / 10; // Variación de ±0.3%
        
        return round($eficienciaBase + $variacion, 1);
    }

    /**
     * Método de prueba para verificar el cálculo sinusoidal
     * Este método se puede eliminar en producción
     * 
     * @param float $capacidad
     * @param float $hora
     * @return array
     */
    public function testSinusoidalCalculation(float $capacidad = 75.0, float $hora = 10.0): array
    {
        // Parámetros de la simulación solar
        $tamanecer = 6.0;
        $tocaso = 18.0;
        
        // Calcular potencia pico
        $ppico = $capacidad * 0.85;
        
        // Aplicar fórmula sinusoidal
        $factorSinusoidal = sin(M_PI * ($hora - $tamanecer) / ($tocaso - $tamanecer));
        $potenciaCalculada = $ppico * $factorSinusoidal;
        
        // Cálculo manual para verificación
        $calculoManual = $ppico * sin(M_PI * (10 - 6) / (18 - 6));
        
        return [
            'capacidad_nominal' => $capacidad,
            'hora_simulada' => $hora,
            'ppico' => $ppico,
            'factor_sinusoidal' => $factorSinusoidal,
            'potencia_calculada' => round($potenciaCalculada, 2),
            'calculo_manual_10am' => round($calculoManual, 2),
            'esperado_10am' => 55.22,
            'diferencia' => round(abs($calculoManual - 55.22), 2)
        ];
    }
}
