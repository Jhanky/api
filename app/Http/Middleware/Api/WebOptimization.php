<?php

namespace App\Http\Middleware\Api;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebOptimization
{
    /**
     * Middleware de optimización específico para frontend web
     * Incluye cache, compresión y optimizaciones de rendimiento
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo aplicar optimizaciones a respuestas JSON exitosas
        if ($response->getStatusCode() === 200 && $response->headers->get('Content-Type') === 'application/json') {
            
            // Agregar headers de cache para datos estáticos
            if ($this->isStaticData($request)) {
                $response->headers->set('Cache-Control', 'public, max-age=300'); // 5 minutos
                $response->headers->set('ETag', md5($response->getContent()));
            }

            // Agregar headers de compresión
            $response->headers->set('Vary', 'Accept-Encoding');
            
            // Agregar headers de seguridad
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            
            // Agregar información de la API
            $response->headers->set('X-API-Version', '1.0.0');
            $response->headers->set('X-Platform', 'web');
        }

        return $response;
    }

    /**
     * Determinar si la petición es para datos estáticos
     *
     * @param Request $request
     * @return bool
     */
    private function isStaticData(Request $request): bool
    {
        $staticRoutes = [
            'api/panels',
            'api/inverters', 
            'api/batteries',
            'api/locations',
            'api/quotation-statuses'
        ];

        $path = $request->path();
        
        foreach ($staticRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }

        return false;
    }
}
