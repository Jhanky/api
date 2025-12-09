<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class SiigoService
{
    private $baseUrl;
    private $username;
    private $accessKey;
    private $partnerId;

    public function __construct()
    {
        $this->baseUrl = config('services.siigo.base_url');
        $this->username = config('services.siigo.username');
        $this->accessKey = config('services.siigo.access_key');
        $this->partnerId = config('services.siigo.partner_id');
    }

    /**
     * Obtiene el token de acceso de Siigo
     * 
     * @return string|null
     * @throws Exception
     */
    public function getAccessToken(): ?string
    {
        try {
            // Verificar si ya tenemos un token válido en caché
            $cachedToken = Cache::get('siigo_access_token');
            if ($cachedToken) {
                // Verificar si el token está próximo a expirar (menos de 1 hora)
                $tokenExpiry = Cache::get('siigo_token_expiry');
                if ($tokenExpiry && now()->diffInMinutes($tokenExpiry) < 60) {
                    Log::info('Token de Siigo próximo a expirar, renovando automáticamente');
                    $this->refreshToken();
                    return Cache::get('siigo_access_token');
                }
                return $cachedToken;
            }

            // No hay token en caché, obtener uno nuevo
            return $this->refreshToken();

        } catch (Exception $e) {
            Log::error('Error en getAccessToken de Siigo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Renueva el token de acceso de Siigo
     * 
     * @return string|null
     * @throws Exception
     */
    public function refreshToken(): ?string
    {
        try {
            // Validar credenciales
            if (!$this->username || !$this->accessKey) {
                throw new Exception('Credenciales de Siigo no configuradas');
            }

            Log::info('Renovando token de Siigo...');

            // Realizar petición de autenticación
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl . '/auth', [
                'username' => $this->username,
                'access_key' => $this->accessKey,
            ]);

            if (!$response->successful()) {
                Log::error('Error al autenticar con Siigo', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new Exception('Error al autenticar con Siigo: ' . $response->body());
            }

            $data = $response->json();
            
            if (!isset($data['access_token'])) {
                throw new Exception('Token de acceso no encontrado en la respuesta de Siigo');
            }

            $accessToken = $data['access_token'];
            $expiresIn = $data['expires_in'] ?? 86400; // Default 24 horas si no se especifica

            // Calcular tiempo de expiración
            $expiryTime = now()->addSeconds($expiresIn);

            // Guardar token y tiempo de expiración en caché
            Cache::put('siigo_access_token', $accessToken, $expiryTime);
            Cache::put('siigo_token_expiry', $expiryTime, $expiryTime);

            Log::info('Token de Siigo renovado exitosamente', [
                'expires_at' => $expiryTime->toISOString(),
                'expires_in_hours' => $expiresIn / 3600
            ]);

            return $accessToken;

        } catch (Exception $e) {
            Log::error('Error al renovar token de Siigo', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Realiza una petición autenticada a la API de Siigo
     * 
     * @param string $method
     * @param string $endpoint
     * @param array $data
     * @return array
     * @throws Exception
     */
    public function makeAuthenticatedRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                throw new Exception('No se pudo obtener el token de acceso');
            }

            $headers = [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
                'Partner-Id' => $this->partnerId,
            ];

            $response = Http::withHeaders($headers);

            switch (strtoupper($method)) {
                case 'GET':
                    $response = $response->get($this->baseUrl . $endpoint, $data);
                    break;
                case 'POST':
                    $response = $response->post($this->baseUrl . $endpoint, $data);
                    break;
                case 'PUT':
                    $response = $response->put($this->baseUrl . $endpoint, $data);
                    break;
                case 'DELETE':
                    $response = $response->delete($this->baseUrl . $endpoint);
                    break;
                default:
                    throw new Exception('Método HTTP no soportado: ' . $method);
            }

            if (!$response->successful()) {
                $errorMessage = 'Error en petición a Siigo';
                
                if ($response->status() === 401) {
                    // Token expirado, limpiar caché y reintentar una vez
                    Cache::forget('siigo_access_token');
                    $errorMessage = 'Token de Siigo expirado. Intenta nuevamente.';
                } elseif ($response->status() === 400) {
                    $errorMessage = 'Solicitud inválida a Siigo. Verifica los parámetros enviados.';
                }

                Log::error('Error en petición a Siigo', [
                    'method' => $method,
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'data' => $data
                ]);

                throw new Exception($errorMessage . ' (Status: ' . $response->status() . ')');
            }

            return $response->json();

        } catch (Exception $e) {
            Log::error('Error en makeAuthenticatedRequest de Siigo', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene productos de Siigo
     * 
     * @param array $filters
     * @return array
     * @throws Exception
     */
    public function getProducts(array $filters = []): array
    {
        try {
            $endpoint = '/v1/products';
            
            // Construir query parameters
            $queryParams = [];
            if (isset($filters['page'])) {
                $queryParams['page'] = $filters['page'];
            }
            if (isset($filters['page_size'])) {
                $queryParams['page_size'] = $filters['page_size'];
            }
            if (isset($filters['name'])) {
                $queryParams['name'] = $filters['name'];
            }
            if (isset($filters['code'])) {
                $queryParams['code'] = $filters['code'];
            }

            return $this->makeAuthenticatedRequest('GET', $endpoint, $queryParams);

        } catch (Exception $e) {
            Log::error('Error al obtener productos de Siigo', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene un producto específico por ID
     * 
     * @param string $productId
     * @return array
     * @throws Exception
     */
    public function getProduct(string $productId): array
    {
        try {
            $endpoint = '/v1/products/' . $productId;
            return $this->makeAuthenticatedRequest('GET', $endpoint);

        } catch (Exception $e) {
            Log::error('Error al obtener producto de Siigo', [
                'product_id' => $productId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene facturas de Siigo
     * 
     * @param array $filters
     * @return array
     * @throws Exception
     */
    public function getInvoices(array $filters = []): array
    {
        try {
            $endpoint = '/v1/invoices';
            
            // Construir query parameters
            $queryParams = [];
            if (isset($filters['page'])) {
                $queryParams['page'] = $filters['page'];
            }
            if (isset($filters['page_size'])) {
                $queryParams['page_size'] = $filters['page_size'];
            }
            if (isset($filters['created_start'])) {
                $queryParams['created_start'] = $filters['created_start'];
            }
            if (isset($filters['created_end'])) {
                $queryParams['created_end'] = $filters['created_end'];
            }
            if (isset($filters['document_id'])) {
                $queryParams['document_id'] = $filters['document_id'];
            }

            return $this->makeAuthenticatedRequest('GET', $endpoint, $queryParams);

        } catch (Exception $e) {
            Log::error('Error al obtener facturas de Siigo', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene una factura específica por ID
     * 
     * @param string $invoiceId
     * @return array
     * @throws Exception
     */
    public function getInvoice(string $invoiceId): array
    {
        try {
            $endpoint = '/v1/invoices/' . $invoiceId;
            return $this->makeAuthenticatedRequest('GET', $endpoint);

        } catch (Exception $e) {
            Log::error('Error al obtener factura de Siigo', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene clientes de Siigo
     * 
     * @param array $filters
     * @return array
     * @throws Exception
     */
    public function getCustomers(array $filters = []): array
    {
        try {
            $endpoint = '/v1/customers';
            
            // Construir query parameters
            $queryParams = [];
            if (isset($filters['page'])) {
                $queryParams['page'] = $filters['page'];
            }
            if (isset($filters['page_size'])) {
                $queryParams['page_size'] = $filters['page_size'];
            }
            if (isset($filters['name'])) {
                $queryParams['name'] = $filters['name'];
            }
            if (isset($filters['document'])) {
                $queryParams['document'] = $filters['document'];
            }

            return $this->makeAuthenticatedRequest('GET', $endpoint, $queryParams);

        } catch (Exception $e) {
            Log::error('Error al obtener clientes de Siigo', [
                'filters' => $filters,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene un cliente específico por ID
     * 
     * @param string $customerId
     * @return array
     * @throws Exception
     */
    public function getCustomer(string $customerId): array
    {
        try {
            $endpoint = '/v1/customers/' . $customerId;
            return $this->makeAuthenticatedRequest('GET', $endpoint);

        } catch (Exception $e) {
            Log::error('Error al obtener cliente de Siigo', [
                'customer_id' => $customerId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Verifica la conectividad con Siigo
     * 
     * @return array
     * @throws Exception
     */
    public function testConnection(): array
    {
        try {
            $accessToken = $this->getAccessToken();
            
            return [
                'success' => true,
                'message' => 'Conexión exitosa con Siigo',
                'has_token' => !empty($accessToken),
                'timestamp' => now()->toISOString()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error de conexión con Siigo: ' . $e->getMessage(),
                'has_token' => false,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Obtiene información del token actual
     * 
     * @return array
     */
    public function getTokenInfo(): array
    {
        $token = Cache::get('siigo_access_token');
        $expiry = Cache::get('siigo_token_expiry');
        
        return [
            'has_token' => !empty($token),
            'token_length' => $token ? strlen($token) : 0,
            'expires_at' => $expiry ? $expiry->toISOString() : null,
            'expires_in_minutes' => $expiry ? now()->diffInMinutes($expiry) : null,
            'is_expired' => $expiry ? now()->isAfter($expiry) : true,
            'needs_refresh' => $expiry ? now()->diffInMinutes($expiry) < 60 : true,
        ];
    }

    /**
     * Fuerza la renovación del token
     * 
     * @return array
     */
    public function forceTokenRefresh(): array
    {
        try {
            // Limpiar token existente
            Cache::forget('siigo_access_token');
            Cache::forget('siigo_token_expiry');
            
            // Obtener nuevo token
            $newToken = $this->refreshToken();
            
            return [
                'success' => true,
                'message' => 'Token renovado exitosamente',
                'has_token' => !empty($newToken),
                'timestamp' => now()->toISOString()
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al renovar token: ' . $e->getMessage(),
                'has_token' => false,
                'timestamp' => now()->toISOString()
            ];
        }
    }

    /**
     * Limpia el token actual del caché
     * 
     * @return bool
     */
    public function clearToken(): bool
    {
        try {
            Cache::forget('siigo_access_token');
            Cache::forget('siigo_token_expiry');
            
            Log::info('Token de Siigo eliminado del caché');
            return true;

        } catch (Exception $e) {
            Log::error('Error al limpiar token de Siigo', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
}
