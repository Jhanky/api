<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\UsedProduct;
use App\Models\Panel;
use App\Models\Inverter;
use App\Models\Battery;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            // Obtener todos los proyectos con su cotización asociada
            $projects = Project::with([
                'quotation' => function($query) {
                    $query->with([
                        'usedProducts' => function($query) {
                            $query->with(['panel', 'inverter', 'battery']);
                        }
                    ]);
                }
            ])->get();

            $result = $projects->map(function ($project) {
                $quotation = $project->quotation;
                $paneles = [];
                $inversores = [];
                $baterias = [];
                
                if ($quotation && $quotation->usedProducts) {
                    foreach ($quotation->usedProducts as $product) {
                        if ($product->product_type === 'panel' && $product->panel) {
                            $marca = $product->panel->brand;
                            $paneles[$marca] = ($paneles[$marca] ?? 0) + $product->quantity;
                        }
                        if ($product->product_type === 'inverter' && $product->inverter) {
                            $marca = $product->inverter->brand;
                            $inversores[$marca] = ($inversores[$marca] ?? 0) + $product->quantity;
                        }
                        if ($product->product_type === 'battery' && $product->battery) {
                            $marca = $product->battery->brand;
                            $baterias[$marca] = ($baterias[$marca] ?? 0) + $product->quantity;
                        }
                    }
                }
                
                return [
                    'id' => $project->id,
                    'nombre_proyecto' => $project->name,
                    'codigo_proyecto' => $project->code,
                    'estado' => $project->status,
                    'fecha_inicio' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                    'fecha_fin' => $project->end_date ? $project->end_date->format('Y-m-d') : null,
                    'cotizacion_id' => $project->quotation_id,
                    'paneles' => $paneles,
                    'inversores' => $inversores,
                    'baterias' => $baterias
                ];
            });

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener proyectos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'code' => 'required|string|max:20',
                'name' => 'required|string|max:100',
                'quotation_id' => 'required|exists:quotations,quotation_id|unique:projects',
                'status' => 'sometimes|in:activo,finalizado,pendiente',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            $project = Project::create($validatedData);
            
            return response()->json([
                'message' => 'Proyecto creado',
                'project' => $project
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear proyecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $project = Project::with([
                'quotation.usedProducts.panel',
                'quotation.usedProducts.inverter', 
                'quotation.usedProducts.battery',
                'purchases'
            ])->findOrFail($id);
            
            return response()->json($project);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Proyecto no encontrado',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);
            
            $validatedData = $request->validate([
                'code' => 'sometimes|string|max:20',
                'name' => 'sometimes|string|max:100',
                'quotation_id' => 'sometimes|exists:quotations,quotation_id|unique:projects,quotation_id,' . $id,
                'status' => 'sometimes|in:activo,finalizado,pendiente',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            $project->update($validatedData);
            
            return response()->json([
                'message' => 'Proyecto actualizado',
                'project' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar proyecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);
            
            // Verificar si el proyecto tiene facturas asociadas
            $purchasesCount = $project->purchases()->count();
            
            if ($purchasesCount > 0) {
                return response()->json([
                    'message' => 'No se puede eliminar el proyecto',
                    'error' => "El proyecto tiene {$purchasesCount} factura(s) asociada(s). Elimine las facturas primero o contacte al administrador."
                ], 400);
            }
            
            $project->delete();
            
            return response()->json([
                'message' => 'Proyecto eliminado'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar proyecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);
            
            $validatedData = $request->validate([
                'status' => 'required|in:activo,finalizado,pendiente'
            ]);

            $project->update($validatedData);
            
            return response()->json([
                'message' => 'Estado del proyecto actualizado',
                'project' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar estado del proyecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateDates(Request $request, $id): JsonResponse
    {
        try {
            $project = Project::findOrFail($id);
            
            $validatedData = $request->validate([
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date'
            ]);

            $project->update($validatedData);
            
            return response()->json([
                'message' => 'Fechas del proyecto actualizadas',
                'project' => $project
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar fechas del proyecto',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}