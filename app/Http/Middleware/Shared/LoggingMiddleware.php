<?php

namespace App\Http\Middleware\Shared;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LoggingMiddleware
{
    /**
     * Middleware de logging compartido
     * Registra peticiones y respuestas para ambas plataformas
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $requestId = $request->header('X-Request-ID', uniqid());
        
        // Agregar request ID a la request
        $request->attributes->set('request_id', $requestId);

        // Log de la petición
        $this->logRequest($request, $requestId);

        $response = $next($request);

        // Log de la respuesta
        $this->logResponse($request, $response, $startTime, $requestId);

        return $response;
    }

    /**
     * Log de la petición entrante
     *
     * @param Request $request
     * @param string $requestId
     */
    private function logRequest(Request $request, string $requestId): void
    {
        $logData = [
            'request_id' => $requestId,
            'method' => $request->method(),
            'url' => $request->fullUrl(),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'platform' => $request->get('platform', 'unknown'),
            'user_id' => $request->user()?->id,
            'timestamp' => now()->toISOString()
        ];

        Log::channel('api')->info('API Request', $logData);
    }

    /**
     * Log de la respuesta
     *
     * @param Request $request
     * @param Response $response
     * @param float $startTime
     * @param string $requestId
     */
    private function logResponse(Request $request, Response $response, float $startTime, string $requestId): void
    {
        $responseTime = round((microtime(true) - $startTime) * 1000, 2); // En milisegundos

        $logData = [
            'request_id' => $requestId,
            'status_code' => $response->getStatusCode(),
            'response_time_ms' => $responseTime,
            'memory_usage' => memory_get_usage(true),
            'platform' => $request->get('platform', 'unknown'),
            'user_id' => $request->user()?->id,
            'timestamp' => now()->toISOString()
        ];

        // Log diferente según el código de respuesta
        if ($response->getStatusCode() >= 400) {
            Log::channel('api')->warning('API Response Error', $logData);
        } else {
            Log::channel('api')->info('API Response', $logData);
        }
    }
}
