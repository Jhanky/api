<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SiigoService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class RefreshSiigoToken extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'siigo:refresh-token 
                            {--force : Forzar renovación incluso si el token es válido}
                            {--test : Solo probar la conexión sin renovar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Renovar el token de acceso de Siigo automáticamente';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔄 Iniciando proceso de renovación de token de Siigo...');

        try {
            $siigoService = new SiigoService();
            
            // Si es solo una prueba, solo verificar conexión
            if ($this->option('test')) {
                return $this->testConnection($siigoService);
            }

            // Verificar si ya hay un token válido (a menos que se fuerce)
            if (!$this->option('force')) {
                $existingToken = Cache::get('siigo_access_token');
                if ($existingToken) {
                    $this->info('✅ Token existente encontrado. Verificando validez...');
                    
                    // Probar el token existente
                    if ($this->testExistingToken($siigoService, $existingToken)) {
                        $this->info('✅ Token existente es válido. No se requiere renovación.');
                        return 0;
                    } else {
                        $this->warn('⚠️ Token existente no es válido. Procediendo con renovación...');
                    }
                }
            }

            // Limpiar token existente si se fuerza la renovación
            if ($this->option('force')) {
                Cache::forget('siigo_access_token');
                $this->info('🗑️ Token anterior eliminado (modo forzado)');
            }

            // Obtener nuevo token
            $this->info('🔑 Obteniendo nuevo token de Siigo...');
            $newToken = $siigoService->getAccessToken();

            if ($newToken) {
                $this->info('✅ Token renovado exitosamente');
                
                // Probar el nuevo token
                if ($this->testNewToken($siigoService)) {
                    $this->info('✅ Nuevo token verificado y funcionando correctamente');
                    
                    // Mostrar información del token
                    $this->displayTokenInfo();
                    
                    Log::info('Token de Siigo renovado exitosamente', [
                        'timestamp' => now()->toISOString(),
                        'forced' => $this->option('force')
                    ]);
                    
                    return 0;
                } else {
                    $this->error('❌ Error: El nuevo token no funciona correctamente');
                    return 1;
                }
            } else {
                $this->error('❌ Error: No se pudo obtener el nuevo token');
                return 1;
            }

        } catch (Exception $e) {
            $this->error('❌ Error durante la renovación del token: ' . $e->getMessage());
            
            Log::error('Error al renovar token de Siigo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()->toISOString()
            ]);
            
            return 1;
        }
    }

    /**
     * Probar conexión con Siigo
     */
    private function testConnection(SiigoService $siigoService): int
    {
        $this->info('🧪 Probando conexión con Siigo...');
        
        $result = $siigoService->testConnection();
        
        if ($result['success']) {
            $this->info('✅ Conexión exitosa con Siigo');
            $this->info('🔑 Token disponible: ' . ($result['has_token'] ? 'Sí' : 'No'));
            return 0;
        } else {
            $this->error('❌ Error de conexión: ' . $result['message']);
            return 1;
        }
    }

    /**
     * Probar token existente
     */
    private function testExistingToken(SiigoService $siigoService, string $token): bool
    {
        try {
            // Intentar hacer una petición simple con el token existente
            $result = $siigoService->makeAuthenticatedRequest('GET', '/v1/products', ['page_size' => 1]);
            return isset($result['results']) || isset($result['data']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Probar nuevo token
     */
    private function testNewToken(SiigoService $siigoService): bool
    {
        try {
            $result = $siigoService->testConnection();
            return $result['success'] && $result['has_token'];
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Mostrar información del token
     */
    private function displayTokenInfo(): void
    {
        $token = Cache::get('siigo_access_token');
        if ($token) {
            $this->info('📊 Información del token:');
            $this->line('   • Longitud: ' . strlen($token) . ' caracteres');
            $this->line('   • Prefijo: ' . substr($token, 0, 10) . '...');
            $this->line('   • Expira: ' . now()->addHour()->format('Y-m-d H:i:s'));
        }
    }
}
