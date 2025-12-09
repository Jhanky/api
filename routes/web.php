<?php

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

Route::get('/', function () {
    return view('welcome');
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
