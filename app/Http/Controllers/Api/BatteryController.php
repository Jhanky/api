<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Battery;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BatteryController extends Controller
{
    use ApiResponseTrait;
    /**
     * Listar todas las baterías con filtros opcionales
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Battery::query();

            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%");
                });
            }

            if ($request->has('brand') && $request->brand) {
                $query->byBrand($request->brand);
            }

            if ($request->has('type') && $request->type) {
                $query->byType($request->type);
            }

            if ($request->has('min_capacity') || $request->has('max_capacity')) {
                $query->byCapacityRange($request->min_capacity, $request->max_capacity);
            }

            if ($request->has('min_voltage') || $request->has('max_voltage')) {
                $query->byVoltageRange($request->min_voltage, $request->max_voltage);
            }

            if ($request->has('min_price') || $request->has('max_price')) {
                $query->byPriceRange($request->min_price, $request->max_price);
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $batteries = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $batteries->items(),
                'pagination' => [
                    'current_page' => $batteries->currentPage(),
                    'per_page' => $batteries->perPage(),
                    'total' => $batteries->total(),
                    'last_page' => $batteries->lastPage(),
                    'from' => $batteries->firstItem(),
                    'to' => $batteries->lastItem(),
                    'has_more_pages' => $batteries->hasMorePages(),
                ],
                'message' => 'Baterías obtenidas exitosamente',
                'timestamp' => now()->toISOString(),
                'request_id' => Str::uuid()->toString()
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener las baterías');
        }
    }

    /**
     * Crear una nueva batería
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'required|string|max:100',
            'model' => 'required|string|max:100',
            'capacity' => 'required|numeric|min:0',
            'voltage' => 'required|numeric|min:0',
            'type' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'technical_sheet' => 'nullable|file|mimes:pdf|max:10240', // 10MB máximo
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $batteryData = [
                'brand' => $request->brand,
                'model' => $request->model,
                'capacity' => $request->capacity,
                'voltage' => $request->voltage,
                'type' => $request->type,
                'price' => $request->price,
            ];

            // Manejar subida de archivo PDF
            if ($request->hasFile('technical_sheet')) {
                $file = $request->file('technical_sheet');
                $filename = 'battery_' . Str::uuid() . '.pdf';
                $path = $file->storeAs('technical_sheets/batteries', $filename, 'public');
                $batteryData['technical_sheet_url'] = $path;
            }

            $battery = Battery::create($batteryData);

            return response()->json([
                'message' => 'Batería creada exitosamente',
                'battery' => $battery
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear la batería',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar una batería específica
     */
    public function show(string $id): JsonResponse
    {
        try {
            $battery = Battery::findOrFail($id);
            return response()->json($battery);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Batería no encontrada',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar una batería
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'brand' => 'sometimes|required|string|max:100',
            'model' => 'sometimes|required|string|max:100',
            'capacity' => 'sometimes|required|numeric|min:0',
            'voltage' => 'sometimes|required|numeric|min:0',
            'type' => 'sometimes|required|string|max:50',
            'price' => 'sometimes|required|numeric|min:0',
            'technical_sheet' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $battery = Battery::findOrFail($id);
            $batteryData = [
                'brand' => $request->get('brand'),
                'model' => $request->get('model'),
                'capacity' => $request->get('capacity'),
                'voltage' => $request->get('voltage'),
                'type' => $request->get('type'),
                'price' => $request->get('price'),
            ];

            // Manejar actualización de archivo PDF
            if ($request->hasFile('technical_sheet')) {
                // Eliminar archivo anterior si existe
                if ($battery->technical_sheet_url) {
                    Storage::disk('public')->delete($battery->technical_sheet_url);
                }

                // Subir nuevo archivo
                $file = $request->file('technical_sheet');
                $filename = 'battery_' . Str::uuid() . '.pdf';
                $path = $file->storeAs('technical_sheets/batteries', $filename, 'public');
                $batteryData['technical_sheet_url'] = $path;
            }

            $battery->update($batteryData);

            return response()->json([
                'message' => 'Batería actualizada exitosamente',
                'battery' => $battery->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la batería',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una batería
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $battery = Battery::findOrFail($id);

            // Eliminar archivo PDF si existe
            if ($battery->technical_sheet_url) {
                Storage::disk('public')->delete($battery->technical_sheet_url);
            }

            $battery->delete();

            return response()->json([
                'message' => 'Batería eliminada exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la batería',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar ficha técnica
     */
    public function downloadTechnicalSheet(string $id): JsonResponse
    {
        try {
            $battery = Battery::findOrFail($id);

            if (!$battery->technical_sheet_url) {
                return response()->json([
                    'message' => 'Esta batería no tiene ficha técnica disponible'
                ], 404);
            }

            if (!Storage::disk('public')->exists($battery->technical_sheet_url)) {
                return response()->json([
                    'message' => 'Archivo de ficha técnica no encontrado'
                ], 404);
            }

            return Storage::disk('public')->download(
                $battery->technical_sheet_url,
                "ficha_tecnica_{$battery->brand}_{$battery->model}.pdf"
            );
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al descargar la ficha técnica',
                'error' => $e->getMessage()
            ], 500);
        }
    }


}
