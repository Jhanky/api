<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PlatformDetection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Detectar plataforma basada en la URL o headers
        $platform = $this->detectPlatform($request);
        
        // Agregar información de plataforma al request
        $request->merge(['platform' => $platform]);
        
        // Agregar headers de respuesta para identificar la plataforma
        $response = $next($request);
        $response->headers->set('X-Platform', $platform);
        $response->headers->set('X-API-Version', '1.0.0');
        
        return $response;
    }

    /**
     * Detectar la plataforma basada en la URL o headers
     *
     * @param Request $request
     * @return string
     */
    private function detectPlatform(Request $request): string
    {
        $path = $request->path();
        
        // Detectar por prefijo de ruta
        if (str_starts_with($path, 'apk/')) {
            return 'mobile';
        }
        
        if (str_starts_with($path, 'api/')) {
            return 'web';
        }
        
        // Detectar por User-Agent si está disponible
        $userAgent = $request->header('User-Agent', '');
        
        if (str_contains($userAgent, 'ReactNative') || 
            str_contains($userAgent, 'Mobile') ||
            str_contains($userAgent, 'Android') ||
            str_contains($userAgent, 'iOS')) {
            return 'mobile';
        }
        
        // Detectar por header personalizado
        $platformHeader = $request->header('X-Platform');
        if ($platformHeader) {
            return $platformHeader === 'mobile' ? 'mobile' : 'web';
        }
        
        // Por defecto, asumir web
        return 'web';
    }
}
