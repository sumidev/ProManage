<?php

namespace App\Http\Controllers;

use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;
use App\Events\TaskMoved;
use App\Http\Requests\TaskRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    protected TaskServiceInterface $taskService;
    protected TaskRepositoryInterface $taskRepository;

     public function __construct(
        TaskServiceInterface $taskService,
        TaskRepositoryInterface $taskRepository
    ) {
        $this->taskService = $taskService;
        $this->taskRepository = $taskRepository;
    }
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
            'type' => 'nullable|in:task,bug,feature,story',
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
            'type' => $request->type ?? 'task',
            'priority' => $request->priority,
            'status' => 'pending',
            'due_date' => $request->due_date,
            'assigned_to' => $request->assigned_to,
            'assigned_by' => $request->user()->id,
            'stage' => $request->stage ?? 'todo',
        ]);

        $task->load('assignedUser:id,first_name,last_name');

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'data' => [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'type' => $task->type ?? 'task',
                'priority' => $task->priority,
                'status' => $task->status,
                'stage' => $task->stage,
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
    public function update(TaskRequest $request, Task $task)
    {
        if ($task->project->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $data = $request->validated();
        $this->taskRepository->updateTask($task, $data);

        $updatedFields = $task->only(array_keys($data));

        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== null) {
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
