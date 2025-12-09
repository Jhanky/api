<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Battery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BatteryController extends Controller
{
    /**
     * Listar todas las baterías con filtros opcionales
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Battery::query();

            // Filtros
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
            $sortBy = $request->get('sort_by', 'battery_id');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $batteries = $query->paginate($perPage);

            return response()->json($batteries);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las baterías',
                'error' => $e->getMessage()
            ], 500);
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
            $batteryData = $validator->validated();
            unset($batteryData['technical_sheet']);

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
            $batteryData = $validator->validated();
            unset($batteryData['technical_sheet']);

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

    /**
     * Obtener estadísticas de baterías
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_batteries' => Battery::count(),
                'brands' => Battery::distinct('brand')->count('brand'),
                'types' => Battery::distinct('type')->count('type'),
                'average_capacity' => Battery::avg('capacity'),
                'average_voltage' => Battery::avg('voltage'),
                'average_price' => Battery::avg('price'),
                'max_capacity' => Battery::max('capacity'),
                'min_capacity' => Battery::min('capacity'),
                'max_voltage' => Battery::max('voltage'),
                'min_voltage' => Battery::min('voltage'),
                'max_price' => Battery::max('price'),
                'min_price' => Battery::min('price'),
                'by_type' => Battery::selectRaw('type, COUNT(*) as count')
                    ->groupBy('type')
                    ->get(),
                'by_brand' => Battery::selectRaw('brand, COUNT(*) as count')
                    ->groupBy('brand')
                    ->orderBy('count', 'desc')
                    ->get(),
            ];

            return response()->json($stats);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
