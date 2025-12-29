<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectStateHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectHistoryController extends Controller
{
    /**
     * Display a listing of project activity (state history and notes).
     */
    public function index(Project $project): JsonResponse
    {
        $history = $project->projectStateHistory()
            ->with(['fromState', 'toState', 'changedBy'])
            ->get()
            ->map(function ($entry) {
                return [
                    'id' => 'h' . $entry->id,
                    'type' => 'status_change',
                    'from_state' => $entry->fromState ? [
                        'id' => $entry->fromState->id,
                        'name' => $entry->fromState->name,
                        'color' => $entry->fromState->color,
                    ] : null,
                    'to_state' => [
                        'id' => $entry->toState->id,
                        'name' => $entry->toState->name,
                        'color' => $entry->toState->color,
                    ],
                    'user' => [
                        'id' => $entry->changedBy->id,
                        'name' => $entry->changedBy->name,
                    ],
                    'date' => $entry->changed_at,
                    'duration_days' => $entry->duration_days,
                    'notes' => $entry->notes,
                ];
            });

        $notes = $project->notes()
            ->with(['user', 'projectState'])
            ->get()
            ->map(function ($note) {
                return [
                    'id' => 'n' . $note->id,
                    'type' => 'comment',
                    'content' => $note->content,
                    'user' => [
                        'id' => $note->user->id,
                        'name' => $note->user->name,
                    ],
                    'state' => $note->projectState ? [
                        'id' => $note->projectState->id,
                        'name' => $note->projectState->name,
                        'color' => $note->projectState->color,
                    ] : null,
                    'date' => $note->created_at,
                ];
            });

        $combined = $history->concat($notes)->sortByDesc('date')->values();

        return response()->json([
            'success' => true,
            'data' => $combined
        ]);
    }

    /**
     * Update the notes for a history entry.
     */
    public function update(Request $request, Project $project, ProjectStateHistory $history): JsonResponse
    {
        if ($history->project_id !== $project->id) {
            return response()->json(['success' => false, 'message' => 'History record not found for this project'], 404);
        }

        $validated = $request->validate([
            'notes' => 'required|string',
        ]);

        $history->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Comentario actualizado correctamente',
            'data' => $history
        ]);
    }

    /**
     * Add a note/comment to the project.
     */
    public function addNote(Request $request, Project $project): JsonResponse
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
        ]);

        $note = \App\Models\ProjectNote::create([
            'project_id' => $project->id,
            'user_id' => auth()->id(),
            'project_state_id' => $project->current_state_id,
            'content' => $validated['note'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comentario agregado correctamente',
            'data' => $note->load(['user', 'projectState'])
        ]);
    }
}
