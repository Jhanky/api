<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\SiigoService;
use Illuminate\Support\Facades\Log;
use Exception;

class VerifySiigoToken
{
    private $siigoService;

    public function __construct(SiigoService $siigoService)
    {
        $this->siigoService = $siigoService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Verificar si el token existe y es válido
            $tokenInfo = $this->siigoService->getTokenInfo();
            
            if (!$tokenInfo['has_token']) {
                Log::warning('No hay token de Siigo disponible, intentando obtener uno nuevo');
                $this->siigoService->refreshToken();
            } elseif ($tokenInfo['is_expired']) {
                Log::warning('Token de Siigo expirado, renovando automáticamente');
                $this->siigoService->forceTokenRefresh();
            } elseif ($tokenInfo['needs_refresh']) {
                Log::info('Token de Siigo próximo a expirar, renovando preventivamente');
                $this->siigoService->refreshToken();
            }

            // Verificar que el token esté funcionando
            $connectionTest = $this->siigoService->testConnection();
            if (!$connectionTest['success']) {
                Log::error('Token de Siigo no funciona correctamente, forzando renovación');
                $this->siigoService->forceTokenRefresh();
                
                // Verificar nuevamente después de la renovación
                $connectionTest = $this->siigoService->testConnection();
                if (!$connectionTest['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se puede conectar con Siigo. Verifica las credenciales.',
                        'error' => 'Siigo connection failed'
                    ], 503);
                }
            }

            // Agregar información del token a la respuesta (opcional)
            $request->attributes->set('siigo_token_info', $tokenInfo);

            return $next($request);

        } catch (Exception $e) {
            Log::error('Error en middleware de verificación de token de Siigo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al verificar token de Siigo',
                'error' => 'Token verification failed'
            ], 500);
        }
    }
}
