<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         $projects = $request->user()->projects()
            ->withCount([
                'tasks',
                'pendingTasks',
                'inProgressTasks',
                'completedTasks'
            ])
            ->latest()
            ->get()
            ->map(function ($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'tasks_count' => $project->tasks_count,
                    'pending_count' => $project->pending_tasks_count,
                    'in_progress_count' => $project->in_progress_tasks_count,
                    'completed_count' => $project->completed_tasks_count,
                    'created_at' => $project->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project = $request->user()->projects()->create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'stage' => $request->stage,
            'status' => $request->status,
            'deadline' => $request->deadline
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'created_at' => $project->created_at->format('Y-m-d'),
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $tasks = $project->tasks()
            ->with('assignedUser:id,name')
            ->get()
            ->groupBy('status')
            ->map(function ($tasks) {
                return $tasks->map(function ($task) {
                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description,
                        'priority' => $task->priority,
                        'status' => $task->status,
                        'due_date' => $task->due_date?->format('Y-m-d'),
                        'assigned_to' => $task->assignedUser ? [
                            'id' => $task->assignedUser->id,
                            'name' => $task->assignedUser->name,
                        ] : null,
                    ];
                });
            });

        return response()->json([
            'success' => true,
            'data' => [
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'owner' => [
                        'id' => $project->user->id,
                        'name' => $project->user->name,
                    ],
                ],
                'tasks' => [
                    'pending' => $tasks->get('pending', collect())->values(),
                    'in_progress' => $tasks->get('in_progress', collect())->values(),
                    'completed' => $tasks->get('completed', collect())->values(),
                ],
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project)
    {
        if ($project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }
}
