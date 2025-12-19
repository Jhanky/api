<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GridType;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GridTypeController extends Controller
{
    use ApiResponseTrait;

    /**
     * Listar todos los tipos de red
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $gridTypes = GridType::active()
                ->select('id', 'name', 'description')
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $gridTypes,
                'message' => 'Tipos de red obtenidos exitosamente',
                'timestamp' => now()->toISOString(),
                'request_id' => \Illuminate\Support\Str::uuid()->toString()
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los tipos de red');
        }
    }
}
