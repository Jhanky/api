<?php

namespace App\Services;

use Carbon\Carbon;

class SolarSimulationService
{
    /**
     * Calcular potencia actual del sistema usando fórmula sinusoidal realista
     * 
     * Fórmula: P(t) = Ppico * sen(π * (t - tamanecer) / (tocaso - tamanecer))
     * 
     * @param float $capacidad Capacidad nominal de la planta en kW
     * @return float Potencia actual en kW
     */
    public function simulateCurrentPower(float $capacidad): float
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
    public function calculateTodayGeneration(float $capacidad): float
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
    public function calculateEfficiency(): int
    {
        // La eficiencia se basa en el factor de pérdidas (0.85 = 85% de eficiencia)
        // Agregamos una pequeña variación realista (±2%) para simular condiciones variables
        $eficienciaBase = 85;
        $variacion = rand(-2, 2);
        
        return max(80, min(90, $eficienciaBase + $variacion));
    }

    /**
     * Calcular eficiencia promedio para estadísticas globales
     * 
     * @return float
     */
    public function calculateAverageEfficiency(): float
    {
        $eficienciaBase = 85.0;
        $variacion = rand(-3, 3) / 10;
        
        return round($eficienciaBase + $variacion, 1);
    }
}
