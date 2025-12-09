<?php

namespace App\Http\Middleware\Shared;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ResponseOptimization
{
    /**
     * Middleware compartido para optimización de respuestas
     * Aplica optimizaciones comunes a ambas plataformas
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo optimizar respuestas JSON exitosas
        if ($response->getStatusCode() === 200 && $response->headers->get('Content-Type') === 'application/json') {
            
            // Comprimir respuesta si es grande
            if (strlen($response->getContent()) > 1024) { // Más de 1KB
                $this->compressResponse($response);
            }

            // Agregar headers de seguridad comunes
            $this->addSecurityHeaders($response);
            
            // Agregar headers de información
            $this->addInfoHeaders($response, $request);
        }

        return $response;
    }

    /**
     * Comprimir respuesta si es necesario
     *
     * @param Response $response
     */
    private function compressResponse(Response $response): void
    {
        $content = $response->getContent();
        
        // Comprimir JSON eliminando espacios innecesarios
        $decoded = json_decode($content, true);
        if ($decoded !== null) {
            $compressed = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $response->setContent($compressed);
        }
    }

    /**
     * Agregar headers de seguridad
     *
     * @param Response $response
     */
    private function addSecurityHeaders(Response $response): void
    {
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Agregar headers de información
     *
     * @param Response $response
     * @param Request $request
     */
    private function addInfoHeaders(Response $response, Request $request): void
    {
        $response->headers->set('X-API-Version', '1.0.0');
        $response->headers->set('X-Response-Time', microtime(true) - LARAVEL_START);
        $response->headers->set('X-Request-ID', $request->header('X-Request-ID', uniqid()));
    }
}
