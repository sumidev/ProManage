<?php

namespace App\Http\Controllers;

use App\Contracts\ProjectRepository\ProjectRepositoryInterface;
use App\Contracts\ProjectRepository\ProjectServiceInterface;
use App\Enums\ProjectStatus;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Requests\InviteMemberRequest;
use Illuminate\Http\Request;
use Exception;

class ProjectController extends Controller
{
    protected ProjectServiceInterface $projectService;
    protected ProjectRepositoryInterface $projectRepository;

    public function __construct(
        ProjectServiceInterface $projectService,
        ProjectRepositoryInterface $projectRepository
    ) {
        $this->projectService = $projectService;
        $this->projectRepository = $projectRepository;
    }

    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $search = $request->query('search');
        $type = $request->query('type');
        $dueDate = $request->query('dueDate');

        $projects = $this->projectRepository->getProjectsForUser($userId, $search, $type, $dueDate);

        $projects->getCollection()->transform(function ($project) use ($userId) {
            return [
                'id' => $project->id,
                'name' => $project->name,
                'description' => $project->description,
                'status' => $project->status,
                'is_owner' => $project->user_id === $userId,
                'type' => $project->type,
                'deadline' => $project->deadline,
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

    public function store(StoreProjectRequest $request)
    {
        $data = $request->validated();
        $data['status'] = ProjectStatus::ACTIVE;
        $data['stages'] = ['backlog', 'todo', 'in_progress', 'review', 'done'];
        $data['user_id'] = $request->user()->id;

        $project = $this->projectRepository->createProject($data);
        $project->members()->attach($request->user()->id, ['role' => 'owner']);

        return response()->json([
            'success' => true,
            'message' => 'Project created successfully',
            'data' => $project,
        ], 201);
    }

    public function show(Request $request, int $projectId)
    {
        try {
            $project = $this->projectRepository->getProjectByIdWithTasks($projectId);
            $kanbanData = $this->projectService->getProjectDetailsForKanban($project, $request->user()->id);

            return response()->json([
                'success' => true,
                'data' => $kanbanData,
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function update(UpdateProjectRequest $request, int $projectId)
    {
        $project = $this->projectRepository->findById($projectId);

        if (!$project || $project->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or Not Found'], 403);
        }

        $validatedData = $request->validated();
        $this->projectRepository->updateProject($project, $validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Project updated successfully',
            'data' => [
                'id' => $project->id,
                'update' => $validatedData
            ],
        ]);
    }

    public function destroy(Request $request, int $projectId)
    {
        $project = $this->projectRepository->findById($projectId);

        if (!$project || $project->user_id !== $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized or Not Found'], 403);
        }

        $this->projectRepository->deleteProject($project);

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully',
        ]);
    }

    public function inviteMember(InviteMemberRequest $request)
    {
        try {
            $invitation = $this->projectService->inviteMemberToProject(
                $request->projectId,
                $request->email,
                $request->user()->id
            );

            return response()->json([
                'success' => true,
                'message' => 'Invitation sent successfully!',
                'data' => $invitation
            ]);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 500);
        }
    }

    public function searchProject(Request $request)
    {
        $projects = $this->projectRepository->searchProjects($request->search ?? '');

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }
}
