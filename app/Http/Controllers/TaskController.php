<?php

namespace App\Http\Controllers;


use App\Contracts\TaskRepository\TaskRepositoryInterface;
use App\Events\TaskMoved;
use App\Http\Requests\TaskRequest;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected TaskRepositoryInterface $taskRepository;

    public function __construct(
        TaskRepositoryInterface $taskRepository
    ) {
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
    public function store(TaskRequest $request, Project $project)
    {
        $data = $request->validated();

        $data['project_id'] = $project->id;
        $data['assigned_by'] = $request->user()->id;
        $data['status'] = 'pending';
        $data['type'] = $data['type'] ?? 'task';
        $data['stage'] = $data['stage'] ?? 'todo';

        $task = $this->taskRepository->createTask($data);

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
                'due_date' => $task->due_date ? Carbon::parse($task->due_date)->format('Y-m-d') : null,
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
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:task,bug,feature,story',
            'priority' => 'sometimes|required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'stage' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $this->taskRepository->updateTask($task, $data);

        $updatedFields = $task->only(array_keys($data));

        if (array_key_exists('assigned_to', $data) && $data['assigned_to'] !== null) {
            $updatedFields['assigned_to'] = $task->project->members()
                ->where('users.id', $data['assigned_to'])
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'email' => $user->email,
                        'role' => $user->pivot->role ?? 'member',
                        'avatar' => $user->profile_pic ?? null,
                    ];
                })
                ->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Task updated successfully',
            'data' => [
                'id' => $task->id,
                'stage' => $task->stage,
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

        $this->taskRepository->updateTask($task, $request->all());

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

        $this->taskRepository->updateTask($task, $request->all());

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

        $this->taskRepository->deleteTask($task);

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ]);
    }
}
