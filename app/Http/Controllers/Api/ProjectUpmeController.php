<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\RequiredDocument;
use App\Models\ProjectDocument;
use Illuminate\Http\Request;

class ProjectUpmeController extends Controller
{
    /**
     * Get UPME data and evidences.
     */
    public function index(Request $request, Project $project)
    {
        // Get requirements for UPME flow
        $requirements = RequiredDocument::where('flow_type', 'UPME')
            ->orderBy('display_order')
            ->get();

        $uploadedDocs = ProjectDocument::where('project_id', $project->id)
            ->whereIn('required_document_id', $requirements->pluck('id'))
            ->active()
            ->get()
            ->keyBy('required_document_id');

        $documents = $requirements->map(function ($req) use ($uploadedDocs) {
            return [
                'required_document' => $req,
                'uploaded_document' => $uploadedDocs->get($req->id)
            ];
        });

        // Get upme detail or return empty structure
        $upmeDetail = $project->upmeDetail;

        return response()->json([
            'upme_data' => [
                'radicado_number' => $upmeDetail?->radicado_number,
                'case_number' => $upmeDetail?->case_number,
                'status' => $upmeDetail?->status ?? 'NO_RADICADO',
                'filing_date' => $upmeDetail?->filing_date ? $upmeDetail->filing_date->format('Y-m-d') : null,
                'filing_comments' => $upmeDetail?->filing_comments,
                'consultation_url' => $upmeDetail?->consultation_url,
                'response_date' => $upmeDetail?->response_date ? $upmeDetail->response_date->format('Y-m-d') : null,
                'response_number' => $upmeDetail?->response_number,
                'response_comments' => $upmeDetail?->response_comments,
            ],
            'evidences' => $documents
        ]);
    }

    /**
     * Update UPME fields.
     */
    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'upme_radicado_number' => 'nullable|string|max:255',
            'upme_case_number' => 'nullable|string|max:255',
            'upme_filing_date' => 'nullable|date',
            'upme_filing_comments' => 'nullable|string',
            'upme_consultation_url' => 'nullable|url|max:255',
            'upme_response_date' => 'nullable|date',
            'upme_response_number' => 'nullable|string|max:255',
            'upme_response_comments' => 'nullable|string',
        ]);

        // Automatic status determination
        $hasRadicado = !empty($validated['upme_radicado_number']) || !empty($validated['upme_case_number']);
        $hasResponse = !empty($validated['upme_response_number']) || !empty($validated['upme_response_date']);
        
        $status = 'NO_RADICADO';
        if ($hasResponse) {
            $status = 'RESPUESTA_RECIBIDA';
        } elseif ($hasRadicado) {
            $status = 'RADICADO';
        }

        // Map frontend fields to DB fields with empty string to null conversion
        $mappedData = [
            'radicado_number' => !empty($validated['upme_radicado_number']) ? $validated['upme_radicado_number'] : null,
            'case_number' => !empty($validated['upme_case_number']) ? $validated['upme_case_number'] : null,
            'status' => $status,
            'filing_date' => !empty($validated['upme_filing_date']) ? $validated['upme_filing_date'] : null,
            'filing_comments' => $validated['upme_filing_comments'] ?? null,
            'consultation_url' => $validated['upme_consultation_url'] ?? null,
            'response_date' => !empty($validated['upme_response_date']) ? $validated['upme_response_date'] : null,
            'response_number' => !empty($validated['upme_response_number']) ? $validated['upme_response_number'] : null,
            'response_comments' => $validated['upme_response_comments'] ?? null,
        ];

        if ($project->upmeDetail) {
            $project->upmeDetail->update($mappedData);
        } else {
            $project->upmeDetail()->create($mappedData);
        }

        return response()->json([
            'message' => 'UPME info updated successfully',
            'upme_data' => $project->fresh()->upmeDetail
        ]);
    }
}
