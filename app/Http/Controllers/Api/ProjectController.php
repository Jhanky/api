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
            // Obtener todos los proyectos con sus relaciones
            $projects = Project::with([
                'quotation.quotationProducts',
                'client',
                'currentState'
            ])->get();

            $result = $projects->map(function ($project) {
                $quotation = $project->quotation;
                $paneles = [];
                $inversores = [];
                $baterias = [];

                // Procesar productos de la cotización usando campos snapshot
                if ($quotation && $quotation->quotationProducts) {
                    foreach ($quotation->quotationProducts as $product) {
                        $specs = $product->snapshot_specs ?? [];
                        
                        if ($product->product_type === 'panel') {
                            $paneles[$product->snapshot_brand ?? 'Sin marca'] = [
                                'cantidad' => $product->quantity,
                                'modelo' => $product->snapshot_model,
                                'potencia' => ($specs['power'] ?? '') . ' W',
                                'tipo' => $specs['type'] ?? null,
                                'precio_unitario' => $product->unit_price_cop,
                                'valor_total' => $product->quantity * $product->unit_price_cop
                            ];
                        }
                        if ($product->product_type === 'inverter') {
                            $inversores[$product->snapshot_brand ?? 'Sin marca'] = [
                                'cantidad' => $product->quantity,
                                'modelo' => $product->snapshot_model,
                                'potencia' => ($specs['power'] ?? '') . ' W',
                                'precio_unitario' => $product->unit_price_cop,
                                'valor_total' => $product->quantity * $product->unit_price_cop
                            ];
                        }
                        if ($product->product_type === 'battery') {
                            $baterias[$product->snapshot_brand ?? 'Sin marca'] = [
                                'cantidad' => $product->quantity,
                                'modelo' => $product->snapshot_model,
                                'capacidad' => ($specs['capacity'] ?? '') . ' Ah',
                                'voltaje' => ($specs['voltage'] ?? '') . ' V',
                                'precio_unitario' => $product->unit_price_cop,
                                'valor_total' => $product->quantity * $product->unit_price_cop
                            ];
                        }
                    }
                }

                return [
                    'id' => $project->id,
                    'nombre_proyecto' => $project->name ?? ($quotation ? ($quotation->project_name ?? 'Sin nombre') : 'Sin cotización'),
                    'codigo_proyecto' => $project->code ?? ('PROY-' . str_pad($project->id, 6, '0', STR_PAD_LEFT)),
                    'estado' => $project->currentState ? [
                        'id' => $project->currentState->id,
                        'name' => $project->currentState->name,
                        'slug' => $project->currentState->slug ?? null,
                        'color' => $project->currentState->color ?? null,
                    ] : null,
                    'power_kwp' => $quotation ? floatval($quotation->power_kwp ?? 0) : null,
                    'panel_count' => $quotation ? intval($quotation->panel_count ?? 0) : null,
                    'total_value' => $quotation ? floatval($quotation->total_value ?? 0) : null,
                    'fecha_inicio' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                    'fecha_fin' => $project->actual_end_date ? $project->actual_end_date->format('Y-m-d') : null,
                    'cotizacion_id' => $project->quotation_id,
                    'cliente' => $project->client ? [
                        'client_id' => $project->client->id,
                        'nombre' => $project->client->name,
                        'nic' => $project->client->nic ?? null,
                        'departamento' => $project->client->department ?? null,
                        'ciudad' => $project->client->city ?? null,
                        'telefono' => $project->client->phone ?? null,
                        'email' => $project->client->email ?? null
                    ] : null,
                    'paneles' => $paneles,
                    'inversores' => $inversores,
                    'baterias' => $baterias,
                    'informacion_tecnica' => [
                        'tipo_sistema' => $quotation ? ($quotation->system_type ?? null) : null,
                        'potencia_total' => $quotation ? floatval($quotation->power_kwp ?? 0) : null,
                        'cantidad_paneles' => $quotation ? intval($quotation->panel_count ?? 0) : null,
                        'presupuesto' => $quotation ? floatval($quotation->total_value ?? 0) : null,
                        'notas' => $project->notes ?? null
                    ]
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
                'quotation.quotationProducts',
                'client',
                'currentState',
                'projectType',
                'department',
                'city'
            ])->findOrFail($id);

            $quotation = $project->quotation;
            $paneles = [];
            $inversores = [];
            $baterias = [];

            // Procesar productos de la cotización usando campos snapshot
            if ($quotation && $quotation->quotationProducts) {
                foreach ($quotation->quotationProducts as $product) {
                    $specs = $product->snapshot_specs ?? [];
                    
                    if ($product->product_type === 'panel') {
                        $paneles[] = [
                            'brand' => $product->snapshot_brand ?? 'Sin marca',
                            'cantidad' => $product->quantity,
                            'modelo' => $product->snapshot_model,
                            'potencia' => $specs['power'] ?? null,
                            'tipo' => $specs['type'] ?? null,
                            'precio_unitario' => $product->unit_price_cop,
                            'valor_total' => $product->quantity * $product->unit_price_cop
                        ];
                    }
                    if ($product->product_type === 'inverter') {
                        $inversores[] = [
                            'brand' => $product->snapshot_brand ?? 'Sin marca',
                            'cantidad' => $product->quantity,
                            'modelo' => $product->snapshot_model,
                            'potencia' => $specs['power'] ?? null,
                            'precio_unitario' => $product->unit_price_cop,
                            'valor_total' => $product->quantity * $product->unit_price_cop
                        ];
                    }
                    if ($product->product_type === 'battery') {
                        $baterias[] = [
                            'brand' => $product->snapshot_brand ?? 'Sin marca',
                            'cantidad' => $product->quantity,
                            'modelo' => $product->snapshot_model,
                            'capacidad' => $specs['capacity'] ?? null,
                            'voltaje' => $specs['voltage'] ?? null,
                            'precio_unitario' => $product->unit_price_cop,
                            'valor_total' => $product->quantity * $product->unit_price_cop
                        ];
                    }
                }
            }

            $result = [
                'id' => $project->id,
                'code' => $project->code,
                'nombre_proyecto' => $project->name ?? ($quotation ? ($quotation->project_name ?? 'Sin nombre') : 'Sin cotización'),
                'description' => $project->description,
                'estado' => $project->currentState ? [
                    'id' => $project->currentState->id,
                    'name' => $project->currentState->name,
                    'slug' => $project->currentState->slug ?? null,
                    'color' => $project->currentState->color ?? null,
                ] : null,
                'project_type' => $project->projectType ? [
                    'id' => $project->projectType->id,
                    'name' => $project->projectType->name,
                ] : null,
                'power_kwp' => $quotation ? floatval($quotation->power_kwp ?? 0) : null,
                'panel_count' => $quotation ? intval($quotation->panel_count ?? 0) : null,
                'total_value' => $quotation ? floatval($quotation->total_value ?? 0) : null,
                'fecha_inicio' => $project->start_date ? $project->start_date->format('Y-m-d') : null,
                'fecha_fin_estimada' => $project->estimated_end_date ? $project->estimated_end_date->format('Y-m-d') : null,
                'fecha_fin' => $project->actual_end_date ? $project->actual_end_date->format('Y-m-d') : null,
                'cotizacion_id' => $project->quotation_id,
                'cotizacion_code' => $quotation ? $quotation->code : null,
                'cliente' => $project->client ? [
                    'id' => $project->client->id,
                    'nombre' => $project->client->name,
                    'nic' => $project->client->nic ?? null,
                    'email' => $project->client->email ?? null,
                    'telefono' => $project->client->phone ?? null,
                ] : null,
                'ubicacion' => [
                    'direccion' => $project->installation_address,
                    'departamento' => $project->department ? $project->department->name : null,
                    'ciudad' => $project->city ? $project->city->name : null,
                    'coordenadas' => $project->coordinates,
                ],
                'paneles' => $paneles,
                'inversores' => $inversores,
                'baterias' => $baterias,
                'notas' => $project->notes,
                'is_active' => $project->is_active,
                'created_at' => $project->created_at ? $project->created_at->format('Y-m-d H:i:s') : null,
                'updated_at' => $project->updated_at ? $project->updated_at->format('Y-m-d H:i:s') : null,
            ];

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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
            
            // Eliminar el proyecto (soft delete gracias al trait SoftDeletes)
            $project->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Proyecto eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
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

    /**
     * Get project statistics.
     */
    public function statistics(): JsonResponse
    {
        try {
            $total = Project::count();
            
            // Obtener estadísticas por estado usando la relación
            $byStatus = [];
            $statuses = \App\Models\ProjectStatus::all();
            
            foreach ($statuses as $status) {
                $byStatus[$status->name] = Project::where('status_id', $status->status_id)->count();
            }

            $stats = [
                'total' => $total,
                'by_status' => $byStatus
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Project statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
