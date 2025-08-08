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
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CostCenterController;
use App\Http\Controllers\Api\ProjectController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Rutas públicas de autenticación
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
});

// Rutas protegidas con autenticación
Route::middleware('auth:sanctum')->group(function () {
    
    // Rutas de autenticación para usuarios autenticados
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
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

    // Rutas de cotizaciones
    Route::apiResource('quotations', QuotationController::class);
    Route::get('quotations/{id}/pdf', [QuotationController::class, 'downloadPDF']);
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

// Rutas para el sistema de facturas
Route::middleware('auth:sanctum')->group(function () {
    // Purchases (Facturas)
    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::get('/purchases/summary', [PurchaseController::class, 'summary']);
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::put('/purchases/{id}', [PurchaseController::class, 'update']);
    Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy']);
    
    // Suppliers (Proveedores)
    Route::apiResource('suppliers', SupplierController::class);
    
    // Cost Centers (Centros de Costo)
    Route::apiResource('cost-centers', CostCenterController::class);
    
    // Projects (Proyectos)
    Route::apiResource('projects', ProjectController::class);
});
// Rutas para Locations/Ciudades
Route::middleware('auth:sanctum')->group(function () {
    // Rutas específicas para select dependientes
    Route::get('/locations/departments', [App\Http\Controllers\Api\LocationController::class, 'getDepartments']);
    Route::get('/locations/cities', [App\Http\Controllers\Api\LocationController::class, 'getCitiesByDepartment']);
    
    // CRUD completo de locations
    Route::apiResource('locations', App\Http\Controllers\Api\LocationController::class);
    
    // Estadísticas de locations
    Route::get('/locations-statistics', [App\Http\Controllers\Api\LocationController::class, 'statistics']);
});

// Rutas adicionales para proyectos (además del apiResource)
Route::put('/projects/{id}/status', [ProjectController::class, 'updateStatus']);
Route::put('/projects/{id}/dates', [ProjectController::class, 'updateDates']);
