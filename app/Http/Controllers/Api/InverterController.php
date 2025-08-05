<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inverter;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InverterController extends Controller
{
    /**
     * Listar todos los inversores con filtros opcionales
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Inverter::query();

            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('system_type', 'like', "%{$search}%")
                      ->orWhere('grid_type', 'like', "%{$search}%");
                });
            }

            if ($request->has('brand')) {
                $query->byBrand($request->get('brand'));
            }

            if ($request->has('system_type')) {
                $query->bySystemType($request->get('system_type'));
            }

            if ($request->has('grid_type')) {
                $query->byGridType($request->get('grid_type'));
            }

            if ($request->has('min_power') || $request->has('max_power')) {
                $query->byPowerRange($request->get('min_power'), $request->get('max_power'));
            }

            if ($request->has('min_price') || $request->has('max_price')) {
                $query->byPriceRange($request->get('min_price'), $request->get('max_price'));
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'inverter_id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $inverters = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Inversores obtenidos exitosamente',
                'data' => $inverters
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los inversores',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear un nuevo inversor
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'power' => 'required|numeric|min:0',
                'system_type' => 'required|string|max:50',
                'grid_type' => 'required|string|max:50',
                'price' => 'required|numeric|min:0',
                'technical_sheet' => 'nullable|file|mimes:pdf|max:10240' // 10MB máximo
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $inverterData = $request->only(['brand', 'model', 'power', 'system_type', 'grid_type', 'price']);

            // Manejar la subida del archivo PDF
            if ($request->hasFile('technical_sheet')) {
                $file = $request->file('technical_sheet');
                $fileName = 'inverters/technical_sheets/' . Str::uuid() . '_' . time() . '.pdf';
                $filePath = $file->storeAs('public', $fileName);
                $inverterData['technical_sheet_url'] = $fileName;
            }

            $inverter = Inverter::create($inverterData);

            return response()->json([
                'success' => true,
                'message' => 'Inversor creado exitosamente',
                'data' => $inverter
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el inversor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un inversor específico
     */
    public function show($id): JsonResponse
    {
        try {
            $inverter = Inverter::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Inversor obtenido exitosamente',
                'data' => $inverter
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Inversor no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar un inversor
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $inverter = Inverter::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'brand' => 'sometimes|string|max:100',
                'model' => 'sometimes|string|max:100',
                'power' => 'sometimes|numeric|min:0',
                'system_type' => 'sometimes|string|max:50',
                'grid_type' => 'sometimes|string|max:50',
                'price' => 'sometimes|numeric|min:0',
                'technical_sheet' => 'nullable|file|mimes:pdf|max:10240'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errores de validación',
                    'errors' => $validator->errors()
                ], 422);
            }

            $inverterData = $request->only(['brand', 'model', 'power', 'system_type', 'grid_type', 'price']);

            // Manejar la actualización del archivo PDF
            if ($request->hasFile('technical_sheet')) {
                // Eliminar el archivo anterior si existe
                if ($inverter->technical_sheet_url && Storage::disk('public')->exists($inverter->technical_sheet_url)) {
                    Storage::disk('public')->delete($inverter->technical_sheet_url);
                }

                // Subir el nuevo archivo
                $file = $request->file('technical_sheet');
                $fileName = 'inverters/technical_sheets/' . Str::uuid() . '_' . time() . '.pdf';
                $filePath = $file->storeAs('public', $fileName);
                $inverterData['technical_sheet_url'] = $fileName;
            }

            $inverter->update($inverterData);

            return response()->json([
                'success' => true,
                'message' => 'Inversor actualizado exitosamente',
                'data' => $inverter->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el inversor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un inversor
     */
    public function destroy($id): JsonResponse
    {
        try {
            $inverter = Inverter::findOrFail($id);

            // Eliminar el archivo PDF si existe
            if ($inverter->technical_sheet_url && Storage::disk('public')->exists($inverter->technical_sheet_url)) {
                Storage::disk('public')->delete($inverter->technical_sheet_url);
            }

            $inverter->delete();

            return response()->json([
                'success' => true,
                'message' => 'Inversor eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el inversor',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar la ficha técnica de un inversor
     */
    public function downloadTechnicalSheet($id): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        try {
            $inverter = Inverter::findOrFail($id);

            if (!$inverter->technical_sheet_url || !Storage::disk('public')->exists($inverter->technical_sheet_url)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ficha técnica no encontrada'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $inverter->technical_sheet_url);
            $fileName = $inverter->brand . '_' . $inverter->model . '_technical_sheet.pdf';

            return response()->download($filePath, $fileName);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al descargar la ficha técnica',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estadísticas de inversores
     */
    public function statistics(): JsonResponse
    {
        try {
            $stats = [
                'total_inverters' => Inverter::count(),
                'brands_count' => Inverter::distinct('brand')->count(),
                'system_types_count' => Inverter::distinct('system_type')->count(),
                'grid_types_count' => Inverter::distinct('grid_type')->count(),
                'average_power' => Inverter::avg('power'),
                'average_price' => Inverter::avg('price'),
                'max_power' => Inverter::max('power'),
                'min_power' => Inverter::min('power'),
                'max_price' => Inverter::max('price'),
                'min_price' => Inverter::min('price'),
                'inverters_by_system_type' => Inverter::selectRaw('system_type, COUNT(*) as count')
                    ->groupBy('system_type')
                    ->get(),
                'inverters_by_grid_type' => Inverter::selectRaw('grid_type, COUNT(*) as count')
                    ->groupBy('grid_type')
                    ->get(),
                'inverters_by_brand' => Inverter::selectRaw('brand, COUNT(*) as count')
                    ->groupBy('brand')
                    ->orderBy('count', 'desc')
                    ->limit(10)
                    ->get()
            ];

            return response()->json([
                'success' => true,
                'message' => 'Estadísticas obtenidas exitosamente',
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las estadísticas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
