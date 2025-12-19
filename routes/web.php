<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Ruta principal - mostrar estado de la API y servicios activos
Route::get('/', function () {
    return view('api-status');
});

// Rutas de autenticación API
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});

// Ruta de prueba para verificar que la API funciona
Route::get('/api-status', function () {
    return response()->json([
        'status' => 'success',
        'message' => 'API funcionando correctamente',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0',
        'endpoints' => [
            'api' => '/api',
            'invoices' => '/api/invoices',
            'test' => '/api/test'
        ]
    ]);
});

// Ruta para mostrar información de la API
Route::get('/api-info', function () {
    return view('api-info');
});

// Ruta de prueba para Inertia.js + React
Route::get('/test-inertia', [App\Http\Controllers\TestController::class, 'index']);
