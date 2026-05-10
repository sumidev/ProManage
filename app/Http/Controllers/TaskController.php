<?php

namespace App\Http\Controllers;

use App\Events\TaskMoved;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Project $project)
    {
        // Authorization check
        if ($project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'stage' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $task = $project->tasks()->create([
            'name' => $request->name,
            'description' => $request->description,
            'priority' => $request->priority,
            'status' => 'pending',
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => $request->user()->id,
            'stage' => $request->stage,
        ]);

        $task->load('assignedUser:id,first_name,last_name');

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'priority' => $task->priority,
                'status' => $task->status,
                'due_date' => $task->due_date?->format('Y-m-d'),
                'assigned_to' => $task->assignedUser ? $task->assignedUser : null,
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Task $task)
    {
        if ($task->project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validatedData = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string',
            'priority'    => 'sometimes|required|in:low,medium,high,critical',
            'stage'       => 'sometimes|required|string',
            'due_date'    => 'sometimes|nullable|date',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ]);

        $task->update($validatedData);

        $updatedFields = $task->only(array_keys($validatedData));

        if (array_key_exists('assigned_to', $validatedData) && $validatedData['assigned_to'] !== null) {
            $updatedFields['assigned_to'] = $task->project->members()
                ->where('users.id', $validatedData['assigned_to'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id'        => $user->id,
                        'first_name' => $user->first_name,
                        'last_name'  => $user->last_name,
                        'email'     => $user->email,
                        'role'      => $user->pivot->role ?? 'member',
                        'avatar'    => $user->profile_pic ?? null,
                    ];
                })
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data'    => [
                'id' => $task->id,
                'stage'  => $task->stage,
                'update' => $updatedFields
            ]
        ]);
    }

    public function updateStatus(Request $request, Task $task)
    {
        // Authorization check
        if ($task->project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,in_progress,completed',
        ]);

        $task->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated successfully',
            'data' => [
                'id' => $task->id,
                'status' => $task->status,
            ],
        ]);
    }

    public function moveTask(Request $request, Task $task)
    {
        $request->validate([
            'stage' => 'required|string',
        ]);

        $task->update([
            'stage' => $request->stage
        ]);

        $task->load('assignedUser');
        broadcast(new TaskMoved($task))->toOthers();

        return response()->json(['message' => 'Task moved successfully', 'task' => $task]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Task $task)
    {
        // Authorization check
        if ($task->project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }
}
