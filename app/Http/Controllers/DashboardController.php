<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function stats(Request $request)
    {
        $userId = auth()->id();
        $today = Carbon::now();

        $projectsQuery = Project::where('user_id', $userId)
            ->orWhereHas('members', function($q) use ($userId) {
                $q->where('users.id', $userId);
            });

        $totalProjects = $projectsQuery->count();

        $myTasksQuery = Task::where('assigned_to', $userId);

        $totalTasks = (clone $myTasksQuery)->count();

        $pendingTasks = (clone $myTasksQuery)
            ->where('stage', '!=', 'done')
            ->count();

        $completedTasks = (clone $myTasksQuery)
            ->where('stage', 'done')
            ->count();

        $overdueTasks = (clone $myTasksQuery)
            ->where('stage', '!=', 'done')
            ->whereDate('due_date', '<', $today)
            ->count();

        $recentProjects = $projectsQuery
            ->latest('updated_at')
            ->take(3)
            ->get(['id', 'name', 'status', 'updated_at']); 

        $myUpcomingTasks = (clone $myTasksQuery)
            ->where('stage', '!=', 'done')
            ->orderBy('due_date', 'asc')
            ->take(3)
            ->with('project:id,name')
            ->get(['id', 'name', 'priority', 'due_date', 'stage', 'project_id']);

        return response()->json([
            'stats' => [
                'totalProjects' => $totalProjects,
                'totalTasks' => $totalTasks,
                'pendingTasks' => $pendingTasks,
                'completedTasks' => $completedTasks,
                'overdueTasks' => $overdueTasks,
            ],
            'recentProjects' => $recentProjects,
            'myTasks' => $myUpcomingTasks
        ]);
    }
}
