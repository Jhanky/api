<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectDocument;
use App\Models\RequiredDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ProjectRequirementsController extends Controller
{
    /**
     * List requirements for the current project state (or specified state) and flow type.
     */
    public function index(Request $request, Project $project)
    {
        $flowType = $request->input('flow_type', 'OPERATOR');
        $allStates = $request->boolean('all_states');

        $query = RequiredDocument::where('flow_type', $flowType);

        if (!$allStates) {
            $stateSlug = $request->input('state', $project->currentState?->slug);
            if ($stateSlug && $flowType === 'OPERATOR') {
                 $query->where('state', $stateSlug);
            }
        }

        $requirements = $query->orderBy('state') // Group by state
                              ->orderBy('display_order')
                              ->get();

        // Load existing uploaded documents for project
        $uploadedDocs = ProjectDocument::where('project_id', $project->id)
            ->where('is_active', true)
            ->get()
            ->groupBy('required_document_id');

        // Map requirements to status
        $data = $requirements->map(function ($req) use ($uploadedDocs) {
            $docs = $uploadedDocs->get($req->id);
            // Get latest uploaded document for this requirement
            $doc = $docs ? $docs->sortByDesc('created_at')->first() : null;
            
            return [
                'required_document' => $req,
                'uploaded_document' => $doc,
                'status' => $doc ? 'UPLOADED' : 'PENDING'
            ];
        });

        return response()->json($data);
    }

    /**
     * Upload a document for a specific requirement.
     */
    public function store(Request $request, Project $project)
    {
        $request->validate([
            'required_document_id' => 'required|exists:required_documents,id',
            'file' => 'required|file|max:10240', // 10MB limit
        ]);

        $reqDoc = RequiredDocument::find($request->required_document_id);
        
        try {
            return DB::transaction(function () use ($request, $project, $reqDoc) {
                // Delete previous if exists? Or keep versions? 
                // For now, let's keep it simple: soft delete old one or just add new one.
                // The frontend might want to see history.
                // Let's mark old ones as inactive or replaced?
                // The logical simple flow is: find existing active doc for this req, delete/archive it.

                $existingDoc = ProjectDocument::where('project_id', $project->id)
                    ->where('required_document_id', $reqDoc->id)
                    ->active()
                    ->first();

                if ($existingDoc) {
                    $existingDoc->update(['is_active' => false]);
                }

                $file = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $extension = $file->getClientOriginalExtension();
                $mimeType = $file->getMimeType();
                $size = $file->getSize();

                // Path: projects/{id}/{flow_type}/{state}/filename
                $path = $file->storeAs(
                    "projects/{$project->id}/documents/{$reqDoc->flow_type}/{$reqDoc->state}",
                    uniqid() . '_' . $originalName
                );

                $doc = $project->projectDocuments()->create([
                    'code' => 'DOC-' . strtoupper(uniqid()), // Generate unique code
                    'required_document_id' => $reqDoc->id,
                    'name' => $reqDoc->name, // Or user provided name
                    'description' => $reqDoc->description,
                    'original_filename' => $originalName,
                    'file_path' => $path,
                    'mime_type' => $mimeType,
                    'file_size' => $size,
                    'file_extension' => $extension,
                    'document_date' => now(),
                    'uploaded_by' => auth()->id(),
                    'is_active' => true,
                    'is_public' => true, // Visible to client? Maybe
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Document uploaded successfully',
                    'document' => $doc
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload failed: ' . $e->getMessage()], 500);
        }
    }

    /**
     * List all documents for the project.
     */
    public function allDocuments(Project $project)
    {
        $documents = ProjectDocument::with(['uploader:id,name', 'requiredDocument:id,name,description,state'])
            ->where('project_id', $project->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents,
            'message' => 'Documentos del proyecto obtenidos exitosamente'
        ]);
    }

    /**
     * Download a project document.
     */
    public function download(Project $project, ProjectDocument $document)
    {
        $path = storage_path('app/' . $document->file_path);
        
        \Log::info('📥 Attempting download (v2):', [
            'project_id' => $project->id,
            'document_id' => $document->id,
            'file_path' => $document->file_path,
            'absolute_path' => $path,
            'exists_storage' => Storage::exists($document->file_path),
            'exists_filesystem' => file_exists($path)
        ]);

        // Verify document belongs to project
        if ($document->project_id != $project->id) {
            \Log::warning('❌ Project mismatch in download');
            return response()->json(['error' => 'Document not found for this project'], 404);
        }

        if (!file_exists($path)) {
            \Log::warning('❌ File not found on filesystem: ' . $path);
            return response()->json(['error' => 'File not found on storage'], 404);
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $document->original_filename . '"'
        ]);
    }
}
