<?php

namespace App\Http\Controllers;

use App\Enums\ProjectStatus;
use App\Events\InvitationCreated;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $search = $request->query('search');

        $projects = Project::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('members', function (Builder $memberQuery) use ($userId) {
                        $memberQuery->where('users.id', $userId);
                    });
            })

            // 2. 🔍 SEARCH GROUP: Name me mile YA description me mile
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            })
            ->withCount([
                'tasks',
                'tasks as pending_tasks_count' => function ($query) {
                    $query->where('stage', 'todo');
                },
                'tasks as in_progress_tasks_count' => function ($query) {
                    $query->where('stage', 'in_progress');
                },
                'tasks as completed_tasks_count' => function ($query) {
                    $query->where('stage', 'done');
                }
            ])
            ->latest()
            ->paginate(9);

        $projects->getCollection()->transform(function ($project) use ($userId) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'is_owner' => $project->user_id === $userId,
                'tasks_count' => $project->tasks_count,
                'pending_count' => $project->pending_tasks_count,
                'in_progress_count' => $project->in_progress_tasks_count,
                'completed_count' => $project->completed_tasks_count,
                'created_at' => $project->created_at,
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
            'type' => 'required|string',
            'deadline' => 'required|date',
        ]);

        $project = $request->user()->projects()->create([
            'name' => $request->name,
            'description' => $request->description,
            'type' => $request->type,
            'deadline' => $request->deadline,
            'status' => ProjectStatus::ACTIVE,
            'stages' =>  ['backlog', 'todo', 'in_progress', 'review', 'done'],
            "user_id" => Auth::user()->id,
        ]);

        $project->members()->attach(Auth::user()->id, ['role' => 'owner']);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => $project,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Project $project)
    {
        $user = $request->user();
        $isOwner = $project->user_id === $user->id;
        $isMember = $project->members()->where('users.id', $user->id)->exists();

        if (!$isOwner && !$isMember) {
            return response()->json(['message' => 'Forbidden: You do not have access to view this project.'], 403);
        }

        $definedStages = $project->stages ?? ['todo', 'in_progress', 'done'];

        $members = $project->members->map(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'role' => $user->pivot->role, // Pivot table se role nikala (admin/member)
                'avatar' => null, // Future me image URL daal dena
            ];
        });

        $tasks = $project->tasks()
            ->with('assignedUser:id,first_name,last_name')
            ->orderBy('order', 'asc') // Kanban me order matter karta hai
            ->get();

        // 4. Tasks ko Group karo (Backend Transformation)
        // Hum pehle tasks ko format kar rahe hain, fir group karenge
        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'priority' => $task->priority,
                'stage' => $task->stage, // Ensure DB column name matches (stage vs status)
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'assigned_to' => $task->assignedUser ? $task->assignedUser : null,
                'comments_count' => 0,
                'comments' => [],
            ];
        });

        // 5. Group by Stage
        $groupedTasks = $formattedTasks->groupBy('stage');

        // 6. Final Structure Banao (Ensure Empty Columns appear)
        $kanbanData = [];

        foreach ($definedStages as $stage) {
            // Har defined stage ke liye check karo task hai ya nahi
            // Agar nahi hai to empty array [] return karo
            $kanbanData[$stage] = $groupedTasks->get($stage, []);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'project' => [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'type' => $project->type,
                    'stages' => $definedStages, // Frontend ko pata hona chahiye columns ka order
                    'deadline' => $project->deadline,
                    'status' => $project->status,
                    'members' => $members,
                ],
                'tasks' => $kanbanData, // { "todo": [...], "testing": [] } (Dynamic Keys)
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

    public function inviteMember(Request $request)
    {
        $request->validate([
            'projectId' => 'required|exists:projects,id',
            'email'     => 'required|email',
        ]);

        $project = Project::findOrFail($request->projectId);

        if ($project->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized: Only the owner can invite members'], 403);
        }

        $user = User::where('email', $request->email)->first();

        if ($user && $project->members()->where('users.id', $user->id)->exists()) {
            return response()->json(['message' => 'User is already a member of this project.'], 409);
        }

        $existingInvite = ProjectInvitation::where('project_id', $project->id)
            ->where('email', $request->email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            return response()->json(['message' => 'An invitation has already been sent to this email.'], 409);
        }

        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'email'      => $request->email,
            'token'      => Str::random(40),
            'status'     => 'pending',
            'invited_by' => $request->user()->id,
        ]);

        InvitationCreated::dispatch($invitation);

        return response()->json([
            'success' => true,
            'message' => 'Invitation sent successfully!',
            'data'    => $invitation
        ]);
    }

    public function searchProject(Request $request)
    {
        $projects = Project::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'LIKE', "%{$search}%")->orWhere('description', 'LIKE', "%{$search}%");
            })
            ->get();

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }
}
