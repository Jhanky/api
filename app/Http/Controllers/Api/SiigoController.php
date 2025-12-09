<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SiigoService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Exception;

class SiigoController extends Controller
{
    private $siigoService;

    public function __construct(SiigoService $siigoService)
    {
        $this->siigoService = $siigoService;
    }

    /**
     * Probar conexión con Siigo
     * 
     * @return JsonResponse
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->siigoService->testConnection();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result
            ], $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al probar conexión con Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener productos de Siigo
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getProducts(Request $request): JsonResponse
    {
        try {
            // Validar parámetros de entrada
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'page_size' => 'integer|min:1|max:100',
                'name' => 'string|max:255',
                'code' => 'string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $filters = $request->only(['page', 'page_size', 'name', 'code']);
            $products = $this->siigoService->getProducts($filters);

            return response()->json([
                'success' => true,
                'message' => 'Productos obtenidos exitosamente',
                'data' => $products
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener productos de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un producto específico de Siigo
     * 
     * @param string $productId
     * @return JsonResponse
     */
    public function getProduct(string $productId): JsonResponse
    {
        try {
            // Validar ID del producto
            if (empty($productId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID del producto es requerido'
                ], 400);
            }

            $product = $this->siigoService->getProduct($productId);

            return response()->json([
                'success' => true,
                'message' => 'Producto obtenido exitosamente',
                'data' => $product
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener producto de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener facturas de Siigo
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getInvoices(Request $request): JsonResponse
    {
        try {
            // Validar parámetros de entrada
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'page_size' => 'integer|min:1|max:100',
                'created_start' => 'date',
                'created_end' => 'date|after_or_equal:created_start',
                'document_id' => 'string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $filters = $request->only(['page', 'page_size', 'created_start', 'created_end', 'document_id']);
            $invoices = $this->siigoService->getInvoices($filters);

            return response()->json([
                'success' => true,
                'message' => 'Facturas obtenidas exitosamente',
                'data' => $invoices
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener facturas de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener una factura específica de Siigo
     * 
     * @param string $invoiceId
     * @return JsonResponse
     */
    public function getInvoice(string $invoiceId): JsonResponse
    {
        try {
            // Validar ID de la factura
            if (empty($invoiceId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID de la factura es requerido'
                ], 400);
            }

            $invoice = $this->siigoService->getInvoice($invoiceId);

            return response()->json([
                'success' => true,
                'message' => 'Factura obtenida exitosamente',
                'data' => $invoice
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener factura de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener clientes de Siigo
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getCustomers(Request $request): JsonResponse
    {
        try {
            // Validar parámetros de entrada
            $validator = Validator::make($request->all(), [
                'page' => 'integer|min:1',
                'page_size' => 'integer|min:1|max:100',
                'name' => 'string|max:255',
                'document' => 'string|max:50',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Parámetros de validación incorrectos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $filters = $request->only(['page', 'page_size', 'name', 'document']);
            $customers = $this->siigoService->getCustomers($filters);

            return response()->json([
                'success' => true,
                'message' => 'Clientes obtenidos exitosamente',
                'data' => $customers
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener clientes de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un cliente específico de Siigo
     * 
     * @param string $customerId
     * @return JsonResponse
     */
    public function getCustomer(string $customerId): JsonResponse
    {
        try {
            // Validar ID del cliente
            if (empty($customerId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID del cliente es requerido'
                ], 400);
            }

            $customer = $this->siigoService->getCustomer($customerId);

            return response()->json([
                'success' => true,
                'message' => 'Cliente obtenido exitosamente',
                'data' => $customer
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener cliente de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información general de la API de Siigo
     * 
     * @return JsonResponse
     */
    public function getApiInfo(): JsonResponse
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Información de la API de Siigo',
                'data' => [
                    'base_url' => config('services.siigo.base_url'),
                    'partner_id' => config('services.siigo.partner_id'),
                    'endpoints' => [
                        'products' => '/api/siigo/products',
                        'invoices' => '/api/siigo/invoices',
                        'customers' => '/api/siigo/customers',
                        'test_connection' => '/api/siigo/test-connection',
                        'token_info' => '/api/siigo/token-info',
                        'refresh_token' => '/api/siigo/refresh-token'
                    ],
                    'authentication' => [
                        'type' => 'Bearer Token',
                        'auto_refresh' => true,
                        'cache_duration' => '24 horas (con 1 hora de margen)',
                        'scheduled_refresh' => 'Cada 24 horas a las 2:00 AM'
                    ],
                    'rate_limits' => [
                        'note' => 'Sujeto a los límites de la API de Siigo'
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información de la API',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener información del token actual
     * 
     * @return JsonResponse
     */
    public function getTokenInfo(): JsonResponse
    {
        try {
            $tokenInfo = $this->siigoService->getTokenInfo();
            
            return response()->json([
                'success' => true,
                'message' => 'Información del token obtenida exitosamente',
                'data' => $tokenInfo
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Forzar renovación del token
     * 
     * @return JsonResponse
     */
    public function refreshToken(): JsonResponse
    {
        try {
            $result = $this->siigoService->forceTokenRefresh();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result
            ], $result['success'] ? 200 : 500);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al renovar token de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Limpiar token actual
     * 
     * @return JsonResponse
     */
    public function clearToken(): JsonResponse
    {
        try {
            $cleared = $this->siigoService->clearToken();
            
            return response()->json([
                'success' => $cleared,
                'message' => $cleared ? 'Token eliminado exitosamente' : 'Error al eliminar token',
                'data' => [
                    'cleared' => $cleared,
                    'timestamp' => now()->toISOString()
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al limpiar token de Siigo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
