<?php

namespace App\Http\Middleware\Apk;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MobileOptimization
{
    /**
     * Middleware de optimización específico para aplicaciones móviles
     * Incluye compresión, cache y optimizaciones de datos
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo aplicar optimizaciones a respuestas JSON exitosas
        if ($response->getStatusCode() === 200 && $response->headers->get('Content-Type') === 'application/json') {
            
            // Agregar headers de cache optimizados para móvil
            $response->headers->set('Cache-Control', 'private, max-age=60'); // 1 minuto para móvil
            
            // Agregar headers de compresión
            $response->headers->set('Vary', 'Accept-Encoding');
            
            // Agregar headers de seguridad para móvil
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            
            // Agregar información específica de móvil
            $response->headers->set('X-API-Version', '1.0.0');
            $response->headers->set('X-Platform', 'mobile');
            $response->headers->set('X-Mobile-Optimized', 'true');
            
            // Agregar headers para PWA si es necesario
            if ($this->isPWARequest($request)) {
                $response->headers->set('X-PWA-Compatible', 'true');
            }
        }

        return $response;
    }

    /**
     * Determinar si la petición es de una PWA
     *
     * @param Request $request
     * @return bool
     */
    private function isPWARequest(Request $request): bool
    {
        $userAgent = $request->header('User-Agent', '');
        $pwaIndicators = ['PWA', 'Progressive Web App', 'Mobile App'];
        
        foreach ($pwaIndicators as $indicator) {
            if (str_contains($userAgent, $indicator)) {
                return true;
            }
        }

        return false;
    }
}
