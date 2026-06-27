<?php

namespace App\Services;


use App\Contracts\ProjectRepository\ProjectRepositoryInterface;
use App\Contracts\ProjectRepository\ProjectServiceInterface;
use App\Models\Project;
use App\Models\ProjectInvitation;
use App\Models\User;
use App\Events\InvitationCreated;
use Illuminate\Support\Str;
use Exception;

class ProjectService implements ProjectServiceInterface
{
    protected ProjectRepositoryInterface $projectRepository;

    public function __construct(ProjectRepositoryInterface $projectRepository)
    {
        $this->projectRepository = $projectRepository;
    }

    public function getProjectDetailsForKanban(Project $project, int $userId): array
    {
        $isOwner = $project->user_id === $userId;
        $isMember = $this->projectRepository->isUserMember($project, $userId);

        if (!$isOwner && !$isMember) {
            throw new Exception('Forbidden: You do not have access to view this project.', 403);
        }

        $definedStages = $project->stages ?? [
            "backlog",
            "todo",
            "in_progress",
            "review",
            "done"
        ];

        $members = $project->members->map(function ($user) {
            return [
                'id' => $user->id,
                'firstName' => $user->first_name,
                'lastName' => $user->last_name,
                'email' => $user->email,
                'role' => $user->pivot->role,
                'avatar' => null,
            ];
        });

        $tasks = $project->tasks;

        $formattedTasks = $tasks->map(function ($task) {
            return [
                'id' => $task->id,
                'name' => $task->name,
                'description' => $task->description,
                'type' => $task->type ?? 'task',
                'priority' => $task->priority,
                'stage' => $task->stage,
                'due_date' => $task->due_date ? $task->due_date->format('Y-m-d') : null,
                'assigned_to' => $task->assignedUser ? $task->assignedUser : null,
                'comments_count' => 0,
                'comments' => [],
            ];
        });

        $groupedTasks = $formattedTasks->groupBy('stage');
        $kanbanData = [];

        foreach ($definedStages as $stage) {
            $kanbanData[$stage] = $groupedTasks->get($stage, []);
        }

        return [
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'type' => $project->type,
                'stages' => $definedStages,
                'deadline' => $project->deadline,
                'status' => $project->status,
                'members' => $members,
            ],
            'tasks' => $kanbanData,
        ];
    }

    public function inviteMemberToProject(int $projectId, string $email, int $inviterId): array
    {
        $project = $this->projectRepository->findById($projectId);

        if (!$project) {
            throw new Exception('Project not found.', 404);
        }

        if ($project->user_id !== $inviterId) {
            throw new Exception('Unauthorized: Only the owner can invite members', 403);
        }

        $user = User::where('email', $email)->first();

        if ($user && $this->projectRepository->isUserMember($project, $user->id)) {
            throw new Exception('User is already a member of this project.', 409);
        }

        $existingInvite = ProjectInvitation::where('project_id', $project->id)
            ->where('email', $email)
            ->where('status', 'pending')
            ->first();

        if ($existingInvite) {
            throw new Exception('An invitation has already been sent to this email.', 409);
        }

        $invitation = ProjectInvitation::create([
            'project_id' => $project->id,
            'email'      => $email,
            'token'      => Str::random(40),
            'status'     => 'pending',
            'invited_by' => $inviterId,
        ]);

        InvitationCreated::dispatch($invitation->loadMissing(['project', 'inviter']));

        return $invitation->toArray();
    }
}
