<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Apk\AuthController;
use App\Http\Controllers\Apk\DashboardController;
use App\Http\Controllers\Apk\ProjectController;
use App\Http\Controllers\Apk\ClientController;

/*
|--------------------------------------------------------------------------
| APK Routes (React Native Mobile App)
|--------------------------------------------------------------------------
|
| Estas rutas están optimizadas para la aplicación móvil React Native.
| Incluyen funcionalidades simplificadas y respuestas optimizadas para móvil.
|
*/

// Rutas públicas de autenticación para móvil
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
});

// Rutas protegidas para móvil
Route::middleware(['auth:sanctum', 'api-mobile'])->group(function () {
    
    // Autenticación móvil
    Route::prefix('auth')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Dashboard móvil
    Route::prefix('dashboard')->group(function () {
        Route::get('summary', [DashboardController::class, 'getMobileSummary']);
        Route::get('projects/active', [DashboardController::class, 'getActiveProjects']);
        Route::get('realtime', [DashboardController::class, 'getRealTimeData']);
    });

    // Proyectos móvil
    Route::prefix('projects')->group(function () {
        Route::get('/', [ProjectController::class, 'index']);
        Route::get('{id}', [ProjectController::class, 'show']);
        Route::get('status/{status}', [ProjectController::class, 'getByStatus']);
    });

    // Clientes móvil
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index']);
        Route::get('{id}', [ClientController::class, 'show']);
        Route::get('statistics', [ClientController::class, 'statistics']);
    });

});
