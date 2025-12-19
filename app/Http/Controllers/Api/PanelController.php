<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Panel;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PanelController extends Controller
{
    use ApiResponseTrait;
    /**
     * Listar todos los paneles con filtros opcionales
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Panel::query();

            // Filtros
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('brand', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhere('type', 'like', "%{$search}%");
                });
            }

            if ($request->has('brand')) {
                $query->byBrand($request->get('brand'));
            }

            if ($request->has('type')) {
                $query->byType($request->get('type'));
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
            $panels = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $panels->items(),
                'pagination' => [
                    'current_page' => $panels->currentPage(),
                    'per_page' => $panels->perPage(),
                    'total' => $panels->total(),
                    'last_page' => $panels->lastPage(),
                    'from' => $panels->firstItem(),
                    'to' => $panels->lastItem(),
                    'has_more_pages' => $panels->hasMorePages(),
                ],
                'message' => 'Paneles obtenidos exitosamente',
                'timestamp' => now()->toISOString(),
                'request_id' => Str::uuid()->toString()
            ]);

        } catch (\Exception $e) {
            return $this->handleException($e, 'Error al obtener los paneles');
        }
    }

    /**
     * Crear un nuevo panel
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'brand' => 'required|string|max:100',
                'model' => 'required|string|max:100',
                'power' => 'required|numeric|min:0',
                'type' => 'required|string|max:50',
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

            $panelData = [
                'brand' => $request->get('brand'),
                'model' => $request->get('model'),
                'power' => $request->get('power'),
                'type' => $request->get('type'),
                'price' => $request->get('price'),
            ];

            // Manejar la subida del archivo PDF
            if ($request->hasFile('technical_sheet')) {
                $file = $request->file('technical_sheet');
                $fileName = 'panels/technical_sheets/' . Str::uuid() . '_' . time() . '.pdf';
                $filePath = $file->storeAs('public', $fileName);
                $panelData['technical_sheet_url'] = $fileName;
            }

            $panel = Panel::create($panelData);

            return response()->json([
                'success' => true,
                'message' => 'Panel creado exitosamente',
                'data' => $panel
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mostrar un panel específico
     */
    public function show($id): JsonResponse
    {
        try {
            $panel = Panel::findOrFail($id);

            return response()->json([
                'success' => true,
                'message' => 'Panel obtenido exitosamente',
                'data' => $panel
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Panel no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualizar un panel
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $panel = Panel::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'brand' => 'sometimes|string|max:100',
                'model' => 'sometimes|string|max:100',
                'power' => 'sometimes|numeric|min:0',
                'type' => 'sometimes|string|max:50',
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

            $panelData = [
                'brand' => $request->get('brand'),
                'model' => $request->get('model'),
                'power' => $request->get('power'),
                'type' => $request->get('type'),
                'price' => $request->get('price'),
            ];

            // Manejar la actualización del archivo PDF
            if ($request->hasFile('technical_sheet')) {
                // Eliminar el archivo anterior si existe
                if ($panel->technical_sheet_url && Storage::disk('public')->exists($panel->technical_sheet_url)) {
                    Storage::disk('public')->delete($panel->technical_sheet_url);
                }

                // Subir el nuevo archivo
                $file = $request->file('technical_sheet');
                $fileName = 'panels/technical_sheets/' . Str::uuid() . '_' . time() . '.pdf';
                $filePath = $file->storeAs('public', $fileName);
                $panelData['technical_sheet_url'] = $fileName;
            }

            $panel->update($panelData);

            return response()->json([
                'success' => true,
                'message' => 'Panel actualizado exitosamente',
                'data' => $panel->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar un panel
     */
    public function destroy($id): JsonResponse
    {
        try {
            $panel = Panel::findOrFail($id);

            // Eliminar el archivo PDF si existe
            if ($panel->technical_sheet_url && Storage::disk('public')->exists($panel->technical_sheet_url)) {
                Storage::disk('public')->delete($panel->technical_sheet_url);
            }

            $panel->delete();

            return response()->json([
                'success' => true,
                'message' => 'Panel eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el panel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Descargar la ficha técnica de un panel
     */
    public function downloadTechnicalSheet($id): \Symfony\Component\HttpFoundation\BinaryFileResponse|JsonResponse
    {
        try {
            $panel = Panel::findOrFail($id);

            if (!$panel->technical_sheet_url || !Storage::disk('public')->exists($panel->technical_sheet_url)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ficha técnica no encontrada'
                ], 404);
            }

            $filePath = storage_path('app/public/' . $panel->technical_sheet_url);
            $fileName = $panel->brand . '_' . $panel->model . '_technical_sheet.pdf';

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
