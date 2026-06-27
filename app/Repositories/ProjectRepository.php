<?php

namespace App\Repositories;

use App\Contracts\ProjectRepository\ProjectRepositoryInterface;
use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ProjectRepository implements ProjectRepositoryInterface
{
    public function getProjectsForUser(int $userId, ?string $search, ?array $type, ?string $dueDate): LengthAwarePaginator
    {
        return Project::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->orWhereHas('members', function (Builder $memberQuery) use ($userId) {
                        $memberQuery->where('users.id', $userId);
                    });
            })
            ->when($search, function ($query, $search) {
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('description', 'LIKE', "%{$search}%");
                });
            })
            ->when($type, function ($query, $type) {
                if (is_array($type) && count($type) > 0) {
                    $query->whereIn('type', $type);
                }
            })
            ->when($dueDate, function ($query, $dueDate) {
                $today = now()->startOfDay();
                $endOfWeek = now()->endOfWeek();

                switch ($dueDate) {
                    case 'today':
                        $query->whereDate('deadline', $today);
                        break;
                    case 'overdue':
                        $query->whereDate('deadline', '<', $today);
                        break;
                    case 'week':
                        $query->whereBetween('deadline', [$today, $endOfWeek]);
                        break;
                    case 'no_date':
                        $query->whereNull('deadline');
                        break;
                }
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
            ->paginate(12);
    }

    public function createProject(array $data): Project
    {
        return Project::create($data);
    }

    public function getProjectByIdWithTasks(int $projectId): Project
    {
        // Find project and eager load tasks with assigned users
        return Project::with(['tasks' => function ($query) {
            $query->orderBy('order', 'asc');
        }, 'tasks.assignedUser:id,first_name,last_name', 'members'])->findOrFail($projectId);
    }

    public function findById(int $projectId): ?Project
    {
        return Project::find($projectId);
    }

    public function updateProject(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    public function deleteProject(Project $project): bool
    {
        return $project->delete();
    }

    public function isUserMember(Project $project, int $userId): bool
    {
        return $project->members()->where('users.id', $userId)->exists();
    }

    public function searchProjects(string $search): Collection
    {
        return Project::query()
            ->where('name', 'LIKE', "%{$search}%")
            ->orWhere('description', 'LIKE', "%{$search}%")
            ->get();
    }
}
