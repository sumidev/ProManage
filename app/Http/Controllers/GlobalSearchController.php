<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GlobalSearchController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2|max:100',
        ]);

        $term = $request->query('q');
        $userId = $request->user()->id;
        $like = '%' . $term . '%';

        $accessibleProjects = fn () => Project::query()->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->orWhereHas('members', function (Builder $memberQuery) use ($userId) {
                    $memberQuery->where('users.id', $userId);
                });
        });

        $projects = $accessibleProjects()
            ->where(function ($query) use ($like) {
                $query->where('name', 'LIKE', $like)
                    ->orWhere('description', 'LIKE', $like);
            })
            ->select('id', 'name', 'description', 'type', 'status', 'deadline')
            ->latest()
            ->limit(8)
            ->get();

        $tasks = Task::query()
            ->whereHas('project', function ($query) use ($userId) {
                $query->where(function ($projectQuery) use ($userId) {
                    $projectQuery->where('user_id', $userId)
                        ->orWhereHas('members', function (Builder $memberQuery) use ($userId) {
                            $memberQuery->where('users.id', $userId);
                        });
                });
            })
            ->where(function ($query) use ($like) {
                $query->where('name', 'LIKE', $like)
                    ->orWhere('description', 'LIKE', $like);
            })
            ->with(['project:id,name'])
            ->select('id', 'name', 'type', 'stage', 'priority', 'project_id')
            ->latest()
            ->limit(12)
            ->get()
            ->map(fn (Task $task) => [
                'id' => $task->id,
                'name' => $task->name,
                'type' => $task->type ?? 'task',
                'stage' => $task->stage,
                'priority' => $task->priority,
                'project_id' => $task->project_id,
                'project_name' => $task->project?->name,
            ]);

        $users = collect();
        if ($request->user()->system_role === 'admin') {
            $users = User::query()
                ->where('id', '!=', $userId)
                ->where(function ($query) use ($like) {
                    $query->where('first_name', 'LIKE', $like)
                        ->orWhere('last_name', 'LIKE', $like)
                        ->orWhere('email', 'LIKE', $like);
                })
                ->select('id', 'first_name', 'last_name', 'email', 'system_role')
                ->limit(6)
                ->get()
                ->map(fn (User $user) => [
                    'id' => $user->id,
                    'name' => trim($user->first_name . ' ' . $user->last_name),
                    'email' => $user->email,
                    'system_role' => $user->system_role,
                ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'projects' => $projects,
                'tasks' => $tasks,
                'users' => $users,
            ],
        ]);
    }
}
