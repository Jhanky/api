<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SystemType;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SystemTypeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Listar todos los tipos de sistema
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $systemTypes = SystemType::active()
                ->select('id', 'name', 'description')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $systemTypes,
                'message' => 'Tipos de sistema obtenidos exitosamente',
                'timestamp' => now()->toISOString(),
                'request_id' => \Illuminate\Support\Str::uuid()->toString()
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los tipos de sistema');
        }
    }
}
