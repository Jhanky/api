<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\PanelController;
use App\Http\Controllers\Api\InverterController;
use App\Http\Controllers\Api\BatteryController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ChatAIController;
use App\Http\Controllers\Api\SiigoController;
use App\Http\Controllers\Api\ProviderController;
use App\Http\Controllers\Api\CostCenterController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LocationController;

/*
|--------------------------------------------------------------------------
| API Routes (React Web Frontend)
|--------------------------------------------------------------------------
|
| Estas rutas están optimizadas para el frontend web React.
| Incluyen funcionalidades completas de administración y gestión.
|
*/

// Ruta de prueba
Route::get('test', function () {
    return response()->json(['message' => 'API funcionando correctamente']);
});

// Ruta de login simple
Route::post('login', function () {
    return response()->json(['message' => 'Login endpoint funcionando']);
});

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rutas protegidas de autenticación
Route::middleware('auth:sanctum')->prefix('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('logout-all', [AuthController::class, 'logoutAll']);
    Route::get('sessions', [AuthController::class, 'activeSessions']);
    Route::delete('sessions/{tokenId}', [AuthController::class, 'logoutSession']);
    Route::get('me', [AuthController::class, 'me']);
});

// Rutas protegidas con autenticación (Web)
Route::middleware(['auth:sanctum', 'api'])->group(function () {
    // Obtener todos los estados de cotización
    Route::get('quotation-statuses', [\App\Http\Controllers\Api\QuotationStatusListController::class, 'index']);
    
    // Rutas de autenticación para usuarios autenticados
    Route::prefix('auth')->group(function () {
        // Rutas movidas al grupo auth
        // Route::post('logout', [AuthController::class, 'logout']);
        // Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Rutas de usuarios
    Route::prefix('users')->group(function () {
        // Rutas básicas CRUD
        Route::get('/', [UserController::class, 'index']); // Listar usuarios
        Route::post('/', [UserController::class, 'store']); // Crear usuario
        Route::get('{user}', [UserController::class, 'show']); // Ver usuario específico
        Route::put('{user}', [UserController::class, 'update']); // Actualizar usuario
        Route::delete('{user}', [UserController::class, 'destroy']); // Eliminar usuario
        
        // Ruta para obtener el ID del usuario autenticado
        Route::get('me/id', [UserController::class, 'getUserId']); // Obtener ID del usuario autenticado
        
        // Ruta para subir avatar
        Route::post('{user}/avatar', [UserController::class, 'uploadAvatar']);
        
        // Rutas para gestión de roles del usuario
        Route::post('{user}/roles', [UserController::class, 'assignRole']); // Asignar rol
        Route::delete('{user}/roles/{role}', [UserController::class, 'removeRole']); // Quitar rol
    });

    // Rutas de roles
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']); // Listar roles
        Route::post('/', [RoleController::class, 'store']); // Crear rol
        Route::get('{role}', [RoleController::class, 'show']); // Ver rol específico
        Route::put('{role}', [RoleController::class, 'update']); // Actualizar rol
        Route::delete('{role}', [RoleController::class, 'destroy']); // Eliminar rol
        
        // Rutas para gestión de permisos del rol
        Route::post('{role}/permissions', [RoleController::class, 'assignPermission']); // Asignar permiso
        Route::delete('{role}/permissions/{permission}', [RoleController::class, 'removePermission']); // Quitar permiso
    });

    // Rutas de permisos
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index']); // Listar permisos
        Route::post('/', [PermissionController::class, 'store']); // Crear permiso
        Route::get('{permission}', [PermissionController::class, 'show']); // Ver permiso específico
        Route::put('{permission}', [PermissionController::class, 'update']); // Actualizar permiso
        Route::delete('{permission}', [PermissionController::class, 'destroy']); // Eliminar permiso
    });

    // Ruta legacy para compatibilidad
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rutas de clientes
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/user/{userId}', [ClientController::class, 'getByUser']);
    Route::get('clients-statistics', [ClientController::class, 'statistics']);

    // Paneles - Movidas aquí para que sean /api/panels
    Route::apiResource('panels', PanelController::class);
    Route::get('panels/{id}/download-technical-sheet', [PanelController::class, 'downloadTechnicalSheet']);
    Route::get('panels-statistics', [PanelController::class, 'statistics']);

    // Inversores
    Route::apiResource('inverters', InverterController::class);
    Route::get('inverters/{id}/download-technical-sheet', [InverterController::class, 'downloadTechnicalSheet']);
    Route::get('inverters-statistics', [InverterController::class, 'statistics']);

    // Baterías
    Route::apiResource('batteries', BatteryController::class);
    Route::get('batteries/{id}/download-technical-sheet', [BatteryController::class, 'downloadTechnicalSheet']);
    Route::get('batteries-statistics', [BatteryController::class, 'statistics']);

    // Rutas de cotizaciones
    Route::apiResource('quotations', QuotationController::class);
    Route::patch('quotations/{id}/status', [QuotationController::class, 'updateStatus']); // Cambiar estado
    Route::get('quotations/{id}/pdf', [QuotationController::class, 'downloadPDF']);

    // Rutas de ubicaciones (locations)
    Route::apiResource('locations', LocationController::class);
    Route::get('locations/departments', [LocationController::class, 'getDepartments']); // Obtener departamentos
    Route::get('locations/cities', [LocationController::class, 'getCitiesByDepartment']); // Obtener ciudades por departamento

    // Rutas de facturas (invoices) - Las rutas específicas deben ir ANTES del apiResource
    Route::get('invoices/report', [InvoiceController::class, 'generateReport']); // Generar reporte de facturas
    Route::get('invoices/test-report', [InvoiceController::class, 'testReportQuery']); // Probar consulta de reporte
    Route::get('invoices-statistics', [InvoiceController::class, 'statistics']); // Estadísticas de facturas
    Route::get('invoices/export', [InvoiceController::class, 'exportToExcel']); // Exportar a Excel
    Route::apiResource('invoices', InvoiceController::class);
    Route::patch('invoices/{id}/status', [InvoiceController::class, 'updateStatus']); // Cambiar estado de factura
    Route::patch('invoices/{id}/cost-center', [InvoiceController::class, 'changeCostCenter']); // Cambiar centro de costo
    Route::patch('invoices/{id}/retention', [InvoiceController::class, 'toggleRetention']); // Aplicar/remover retención
    Route::post('invoices/{id}/upload-files', [InvoiceController::class, 'uploadFiles']); // Subir archivos
    Route::delete('invoices/{id}/remove-files', [InvoiceController::class, 'removeFiles']); // Eliminar archivos

    // Rutas de centros de costos
    Route::apiResource('cost-centers', CostCenterController::class);
    Route::get('cost-centers/search', [CostCenterController::class, 'search']); // Búsqueda de centros de costos
    Route::get('cost-centers/{id}/invoices', [CostCenterController::class, 'invoices']); // Facturas del centro de costo
    Route::get('cost-centers-statistics', [CostCenterController::class, 'statistics']); // Estadísticas de centros de costos

    // Rutas de proveedores
    Route::apiResource('providers', ProviderController::class);
    Route::get('providers/search', [ProviderController::class, 'search']); // Búsqueda de proveedores
    Route::get('providers/{id}/invoices', [ProviderController::class, 'invoices']); // Facturas del proveedor
    Route::get('providers-statistics', [ProviderController::class, 'statistics']); // Estadísticas de proveedores

    // Rutas del Dashboard/Página de inicio
    Route::prefix('dashboard')->group(function () {
        Route::get('projects', [DashboardController::class, 'getProjects']); // Todos los proyectos
        Route::get('projects/active', [DashboardController::class, 'getActiveProjects']); // Solo proyectos activos
        Route::get('stats', [DashboardController::class, 'getDashboardStats']); // Estadísticas generales
        
        // Ruta temporal para probar el cálculo sinusoidal
        Route::get('test-sinusoidal', [DashboardController::class, 'testSinusoidalCalculation']);
    });

    // Rutas de proyectos
    Route::get('projects', [\App\Http\Controllers\Api\ProjectController::class, 'index']); // Listar proyectos
    Route::get('projects/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'show']); // Mostrar proyecto específico
    Route::put('projects/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'update']); // Actualizar proyecto
    
    // Rutas de imágenes de proyectos
    Route::prefix('projects/{project}/images')->group(function () {
        Route::post('cover', [\App\Http\Controllers\Api\ProjectImageController::class, 'uploadCoverImage']); // Subir imagen de portada
        Route::delete('cover', [\App\Http\Controllers\Api\ProjectImageController::class, 'deleteCoverImage']); // Eliminar imagen de portada
        Route::get('cover', [\App\Http\Controllers\Api\ProjectImageController::class, 'getCoverImage']); // Obtener imagen de portada
    });

    // Rutas de integración con Siigo
    Route::prefix('siigo')->group(function () {
        // Rutas sin middleware (para pruebas y configuración)
        Route::get('test-connection', [SiigoController::class, 'testConnection']);
        Route::get('info', [SiigoController::class, 'getApiInfo']);
        Route::get('token-info', [SiigoController::class, 'getTokenInfo']);
        Route::post('refresh-token', [SiigoController::class, 'refreshToken']);
        Route::delete('clear-token', [SiigoController::class, 'clearToken']);
        
        // Rutas con middleware de verificación de token
        Route::middleware('siigo.token')->group(function () {
            Route::get('products', [SiigoController::class, 'getProducts']);
            Route::get('products/{productId}', [SiigoController::class, 'getProduct']);
            Route::get('invoices', [SiigoController::class, 'getInvoices']);
            Route::get('invoices/{invoiceId}', [SiigoController::class, 'getInvoice']);
            Route::get('customers', [SiigoController::class, 'getCustomers']);
            Route::get('customers/{customerId}', [SiigoController::class, 'getCustomer']);
        });
    });
});

// Rutas adicionales para administración (requieren permisos específicos)
Route::middleware(['auth:sanctum', 'check.permission:admin'])->prefix('admin')->group(function () {
    
    // Estadísticas del sistema
    Route::get('stats', function () {
        return response()->json([
            'users_count' => \App\Models\User::count(),
            'roles_count' => \App\Models\Role::count(),
            'permissions_count' => \App\Models\Permission::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
        ]);
    });
    
    // Exportar usuarios
    Route::get('users/export', [UserController::class, 'export']);
    
    // Importar usuarios
    Route::post('users/import', [UserController::class, 'import']);
    
});

// Rutas adicionales para administración (requieren permisos específicos)
Route::middleware(['auth:sanctum', 'check.permission:admin'])->prefix('admin')->group(function () {
    
    // Estadísticas del sistema
    Route::get('stats', function () {
        return response()->json([
            'users_count' => \App\Models\User::count(),
            'roles_count' => \App\Models\Role::count(),
            'permissions_count' => \App\Models\Permission::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
        ]);
    });
    
    // Exportar usuarios
    Route::get('users/export', [UserController::class, 'export']);
    
    // Importar usuarios
    Route::post('users/import', [UserController::class, 'import']);
    
});
