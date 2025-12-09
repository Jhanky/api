<?php

namespace App\Http\Middleware\Apk;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response;

class MobileRateLimit
{
    /**
     * Rate limiting específico para aplicaciones móviles
     * Límites más permisivos pero con control de abuso
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, int $maxAttempts = 100, int $decayMinutes = 1): Response
    {
        $key = $this->resolveRequestSignature($request);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $retryAfter = RateLimiter::availableIn($key);
            
            return response()->json([
                'success' => false,
                'message' => 'Demasiadas peticiones. Intenta nuevamente en ' . $retryAfter . ' segundos.',
                'error' => 'Too Many Requests',
                'retry_after' => $retryAfter,
                'limit' => $maxAttempts,
                'remaining' => 0
            ], 429);
        }

        RateLimiter::hit($key, $decayMinutes * 60);

        $response = $next($request);

        // Agregar información de rate limit a la respuesta
        $remaining = RateLimiter::remaining($key, $maxAttempts);
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $remaining);
        $response->headers->set('X-RateLimit-Reset', now()->addMinutes($decayMinutes)->timestamp);

        return $response;
    }

    /**
     * Resolver la firma de la petición para rate limiting
     *
     * @param Request $request
     * @return string
     */
    protected function resolveRequestSignature(Request $request): string
    {
        $user = $request->user();
        
        if ($user) {
            return 'mobile:' . $user->id . ':' . $request->ip();
        }

        return 'mobile:guest:' . $request->ip();
    }
}
