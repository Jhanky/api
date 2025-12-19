<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inverter;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InverterController extends Controller
{
    use ApiResponseTrait;
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
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $inverters = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $inverters->items(),
                'pagination' => [
                    'current_page' => $inverters->currentPage(),
                    'per_page' => $inverters->perPage(),
                    'total' => $inverters->total(),
                    'last_page' => $inverters->lastPage(),
                    'from' => $inverters->firstItem(),
                    'to' => $inverters->lastItem(),
                    'has_more_pages' => $inverters->hasMorePages(),
                ],
                'message' => 'Inversores obtenidos exitosamente',
                'timestamp' => now()->toISOString(),
                'request_id' => Str::uuid()->toString()
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los inversores');
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

            $inverterData = [
                'brand' => $request->get('brand'),
                'model' => $request->get('model'),
                'power' => $request->get('power'),
                'system_type' => $request->get('system_type'),
                'grid_type' => $request->get('grid_type'),
                'price' => $request->get('price'),
            ];

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

            // Debug logging for FormData
            $allData = $request->all();
            \Log::info('Inverter update request data:', [
                'request_method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'all_data' => $allData,
                'all_data_keys' => array_keys($allData),
                'brand_from_all' => $allData['brand'] ?? 'NOT_FOUND',
                'model_from_all' => $allData['model'] ?? 'NOT_FOUND',
                'power_from_all' => $allData['power'] ?? 'NOT_FOUND',
                'system_type_from_all' => $allData['system_type'] ?? 'NOT_FOUND',
                'grid_type_from_all' => $allData['grid_type'] ?? 'NOT_FOUND',
                'price_from_all' => $allData['price'] ?? 'NOT_FOUND',
                'has_file' => $request->hasFile('technical_sheet'),
                'file_info' => $request->file('technical_sheet') ? 'File present' : 'No file'
            ]);

            $validator = Validator::make([
                'brand' => $allData['brand'] ?? null,
                'model' => $allData['model'] ?? null,
                'power' => $allData['power'] ?? null,
                'system_type' => $allData['system_type'] ?? null,
                'grid_type' => $allData['grid_type'] ?? null,
                'price' => $allData['price'] ?? null,
                'technical_sheet' => $request->file('technical_sheet')
            ], [
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

            $inverterData = [
                'brand' => $allData['brand'] ?? null,
                'model' => $allData['model'] ?? null,
                'power' => $allData['power'] ?? null,
                'system_type' => $allData['system_type'] ?? null,
                'grid_type' => $allData['grid_type'] ?? null,
                'price' => $allData['price'] ?? null,
            ];

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


}
