<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ClientContactPersonController;
use App\Http\Controllers\Api\ClientTypeController;
use App\Http\Controllers\Api\PanelController;
use App\Http\Controllers\Api\InverterController;
use App\Http\Controllers\Api\BatteryController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\SiigoController;
use App\Http\Controllers\Api\SupplierController;
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

// Endpoint de health check (sin autenticación)
Route::get('health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rutas públicas de ubicaciones (sin autenticación)
Route::prefix('locations')->group(function () {
    Route::get('departments', [LocationController::class, 'getDepartments']);
    Route::get('cities', [LocationController::class, 'getCitiesByDepartment']);
});

// Rutas públicas de tipos de cliente (sin autenticación, para formularios)
Route::prefix('client-types')->group(function () {
    Route::get('active', [ClientTypeController::class, 'active']); // Tipos activos para dropdowns
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

    // Rutas de usuarios - SOLO ADMINISTRADORES
    Route::middleware('api.role:admin')->prefix('users')->group(function () {
        // Rutas específicas que no usan parámetros deben ir ANTES de las que sí usan
        // Ruta para estadísticas de usuarios
        Route::get('statistics', [UserController::class, 'statistics']); // Estadísticas de usuarios

        // Ruta para obtener opciones de usuarios (para dropdowns) - disponible para todos los roles
        Route::get('options', [UserController::class, 'getUserOptions']); // Opciones de usuarios

        // Rutas básicas CRUD - SOLO ADMINISTRADORES
        Route::get('/', [UserController::class, 'index']); // Listar usuarios
        Route::post('/', [UserController::class, 'store']); // Crear usuario
        Route::get('{user}', [UserController::class, 'show']); // Ver usuario específico
        Route::put('{user}', [UserController::class, 'update']); // Actualizar usuario
        Route::patch('{user}/toggle-status', [UserController::class, 'toggleStatus']); // Cambiar estado del usuario
        Route::delete('{user}', [UserController::class, 'destroy']); // Eliminar usuario

        // Ruta para subir avatar
        Route::post('{user}/avatar', [UserController::class, 'uploadAvatar']);

        // Rutas para gestión de roles del usuario
        Route::post('{user}/roles', [UserController::class, 'assignRole']); // Asignar rol
        Route::delete('{user}/roles/{role}', [UserController::class, 'removeRole']); // Quitar rol
    });

    // Rutas de usuarios disponibles para todos los usuarios autenticados
    Route::prefix('users')->group(function () {
        // Ruta para obtener el ID del usuario autenticado
        Route::get('me/id', [UserController::class, 'getUserId']); // Obtener ID del usuario autenticado
        // Ruta para actualizar el tema del usuario autenticado
        Route::patch('theme', [UserController::class, 'updateTheme']); // Actualizar tema del usuario
    });

    // Rutas de roles disponibles para usuarios autenticados
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index']); // Listar roles (disponible para todos los autenticados)
        Route::get('active', [RoleController::class, 'getActiveRoles']); // Roles activos (disponible para todos los autenticados)
        Route::get('permissions', [RoleController::class, 'getPermissions']); // Permisos disponibles (disponible para todos los autenticados)
    });

    // Rutas de roles - SOLO ADMINISTRADORES
    Route::middleware('api.role:admin')->prefix('roles')->group(function () {
        Route::get('statistics', [RoleController::class, 'statistics']); // Estadísticas de roles
        Route::post('/', [RoleController::class, 'store']); // Crear rol
        Route::get('{role}', [RoleController::class, 'show']); // Ver rol específico
        Route::put('{role}', [RoleController::class, 'update']); // Actualizar rol
        Route::patch('{role}/toggle-status', [RoleController::class, 'toggleStatus']); // Cambiar estado del rol
        Route::delete('{role}', [RoleController::class, 'destroy']); // Eliminar rol

        // Rutas de permisos eliminadas - permisos ahora manejados dentro de roles
    });

    // Rutas de permisos eliminadas - permisos ahora manejados dentro de roles

    // Ruta legacy para compatibilidad
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Rutas de clientes
    Route::apiResource('clients', ClientController::class);
    Route::get('clients/user/{userId}', [ClientController::class, 'getByUser']);
    Route::get('clients-statistics', [ClientController::class, 'statistics']);
    Route::delete('clients/bulk', [ClientController::class, 'bulkDelete']);

    // Rutas de tipos de cliente
    Route::apiResource('client-types', ClientTypeController::class);
    Route::get('client-types-statistics', [ClientTypeController::class, 'statistics']);

    // Rutas de personas de contacto
    Route::prefix('clients/{clientId}/contacts')->group(function () {
        Route::get('/', [ClientContactPersonController::class, 'index']); // Contactos de un cliente
        Route::post('/', [ClientContactPersonController::class, 'store']); // Crear contacto
        Route::get('primary', [ClientContactPersonController::class, 'getPrimary']); // Obtener contacto principal (debe ir antes de {contactId})
        Route::get('{contactId}', [ClientContactPersonController::class, 'show']); // Ver contacto específico
        Route::put('{contactId}', [ClientContactPersonController::class, 'update']); // Actualizar contacto
        Route::delete('{contactId}', [ClientContactPersonController::class, 'destroy']); // Eliminar contacto
        Route::patch('{contactId}/set-primary', [ClientContactPersonController::class, 'setPrimary']); // Establecer como principal
    });

    // Rutas técnicas
    // Paneles
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

    // Tipos de sistema y red
    Route::get('system-types', [\App\Http\Controllers\Api\SystemTypeController::class, 'index']);
    Route::get('grid-types', [\App\Http\Controllers\Api\GridTypeController::class, 'index']);

    // Rutas de cotizaciones
    Route::get('quotations/statistics', [QuotationController::class, 'statistics']); // Estadísticas (ANTES de apiResource)
    Route::apiResource('quotations', QuotationController::class);
    Route::patch('quotations/{id}/status', [QuotationController::class, 'updateStatus']); // Cambiar estado
    Route::get('quotations/{id}/pdf', [QuotationController::class, 'downloadPDF']);



    // Rutas financieras
    // Rutas de facturas (invoices) - Las rutas específicas deben ir ANTES del apiResource
    Route::get('invoices/report', [InvoiceController::class, 'generateReport']); // Generar reporte de facturas
    Route::get('invoices/test-report', [InvoiceController::class, 'testReportQuery']); // Probar consulta de reporte
    Route::get('invoices/cost-centers-projects', [InvoiceController::class, 'getCostCentersAndProjects']); // Centros de costo + proyectos unificados ANTES de apiResource
    Route::get('invoices-statistics', [InvoiceController::class, 'statistics']); // Estadísticas de facturas
    Route::get('invoices/export', [InvoiceController::class, 'exportToExcel']); // Exportar a Excel
    Route::apiResource('invoices', InvoiceController::class);
    Route::post('invoices/with-supplier', [InvoiceController::class, 'storeWithSupplier']); // Crear factura con proveedor automático
    Route::patch('invoices/{id}/status', [InvoiceController::class, 'updateStatus']); // Cambiar estado de factura

    Route::patch('invoices/{id}/cost-center', [InvoiceController::class, 'changeCostCenter']); // Cambiar centro de costo
    Route::patch('invoices/{id}/retention', [InvoiceController::class, 'toggleRetention']); // Aplicar/remover retención
    Route::post('invoices/{id}/upload-files', [InvoiceController::class, 'uploadFiles']); // Subir archivos
    Route::delete('invoices/{id}/remove-files', [InvoiceController::class, 'removeFiles']); // Eliminar archivos
    Route::get('invoices/{id}/download', [InvoiceController::class, 'downloadFile']); // Descargar archivo de factura

    // Rutas de centros de costos
    Route::apiResource('cost-centers', CostCenterController::class);
    Route::get('cost-centers/search', [CostCenterController::class, 'search']); // Búsqueda de centros de costos
    Route::get('cost-centers/{id}/evolution', [CostCenterController::class, 'evolution']); // Evolución mensual
    Route::get('cost-centers/{id}/invoices', [CostCenterController::class, 'invoices']); // Facturas del centro de costo
    Route::get('cost-centers-statistics', [CostCenterController::class, 'statistics']); // Estadísticas de centros de costos

    // Rutas de proveedores (suppliers)
    Route::apiResource('suppliers', SupplierController::class);
    Route::get('suppliers/search', [SupplierController::class, 'search']); // Búsqueda de proveedores
    Route::get('suppliers/{id}/invoices', [SupplierController::class, 'invoices']); // Facturas del proveedor
    Route::get('suppliers-statistics', [SupplierController::class, 'statistics']); // Estadísticas de proveedores

    // Rutas del Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('projects', [DashboardController::class, 'getProjects']); // Todos los proyectos
        Route::get('projects/active', [DashboardController::class, 'getActiveProjects']); // Solo proyectos activos
        Route::get('stats', [DashboardController::class, 'getDashboardStats']); // Estadísticas generales

        // Ruta temporal para probar el cálculo sinusoidal
        Route::get('test-sinusoidal', [DashboardController::class, 'testSinusoidalCalculation']);
    });

    // Rutas de estados de proyecto
    Route::get('project-states', [\App\Http\Controllers\Api\ProjectStateController::class, 'index']); // Listar estados de proyecto
    Route::get('project-states/{id}', [\App\Http\Controllers\Api\ProjectStateController::class, 'show']); // Ver estado específico

    // Rutas de proyectos
    Route::get('projects/statistics', [\App\Http\Controllers\Api\ProjectController::class, 'statistics']); // Estadísticas de proyectos
    Route::get('projects', [\App\Http\Controllers\Api\ProjectController::class, 'index']); // Listar proyectos
    Route::post('projects', [\App\Http\Controllers\Api\ProjectController::class, 'store']); // Crear proyecto
    Route::get('projects/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'show']); // Mostrar proyecto específico
    Route::put('projects/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'update']); // Actualizar proyecto
    Route::delete('projects/{project}', [\App\Http\Controllers\Api\ProjectController::class, 'destroy']); // Eliminar proyecto
    Route::patch('projects/{project}/status', [\App\Http\Controllers\Api\ProjectController::class, 'updateStatus']); // Cambiar estado del proyecto
    Route::get('projects/{project}/history', [\App\Http\Controllers\Api\ProjectHistoryController::class, 'index']);
    Route::post('projects/{project}/history/note', [\App\Http\Controllers\Api\ProjectHistoryController::class, 'addNote']);
    Route::patch('projects/{project}/history/{history}', [\App\Http\Controllers\Api\ProjectHistoryController::class, 'update']);

    // Rutas de requisitos de documentos
    Route::get('projects/{project}/requirements', [\App\Http\Controllers\Api\ProjectRequirementsController::class, 'index']);
    Route::post('projects/{project}/documents', [\App\Http\Controllers\Api\ProjectRequirementsController::class, 'store']);
    Route::get('projects/{project}/documents', [\App\Http\Controllers\Api\ProjectRequirementsController::class, 'allDocuments']);
    Route::get('projects/{project}/documents/{document}/download', [\App\Http\Controllers\Api\ProjectRequirementsController::class, 'download']);

    // Rutas UPME
    Route::patch('projects/{project}/upme', [\App\Http\Controllers\Api\ProjectUpmeController::class, 'update']);
    Route::get('projects/{project}/upme', [\App\Http\Controllers\Api\ProjectUpmeController::class, 'index']);

    // Rutas de imágenes de proyectos
    Route::prefix('projects/{project}/images')->group(function () {
        Route::post('cover', [\App\Http\Controllers\Api\ProjectImageController::class, 'uploadCoverImage']); // Subir imagen de portada
        Route::delete('cover', [\App\Http\Controllers\Api\ProjectImageController::class, 'deleteCoverImage']); // Eliminar imagen de portada
        Route::get('cover', [\App\Http\Controllers\Api\ProjectImageController::class, 'getCoverImage']); // Obtener imagen de portada
    });

    // Rutas de integración con Siigo - DESHABILITADAS
    /*
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
    */
});

// Rutas adicionales para administración - DESHABILITADAS
/*
Route::middleware(['auth:sanctum', 'api.role:admin'])->prefix('admin')->group(function () {

    // Estadísticas del sistema
    Route::get('stats', function () {
        return response()->json([
            'users_count' => \App\Models\User::count(),
            'roles_count' => \App\Models\Role::count(),
            'active_users' => \App\Models\User::where('is_active', true)->count(),
        ]);
    });

    // Exportar usuarios
    Route::get('users/export', [UserController::class, 'export']);

    // Importar usuarios
    Route::post('users/import', [UserController::class, 'import']);

});
*/
