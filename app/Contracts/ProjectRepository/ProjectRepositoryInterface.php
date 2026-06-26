<?php

namespace App\Contracts;

use App\Models\Project;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ProjectRepositoryInterface
{
    /**
     * Get paginated projects for a user.
     */
    public function getProjectsForUser(int $userId, ?string $search, ?array $type, ?string $dueDate): LengthAwarePaginator;

    /**
     * Create a new project.
     */
    public function createProject(array $data): Project;

    /**
     * Get a project by ID with its tasks and members.
     */
    public function getProjectByIdWithTasks(int $projectId): Project;

    /**
     * Find a project by ID.
     */
    public function findById(int $projectId): ?Project;

    /**
     * Update a project.
     */
    public function updateProject(Project $project, array $data): bool;

    /**
     * Delete a project.
     */
    public function deleteProject(Project $project): bool;

    /**
     * Check if a user is a member of the project.
     */
    public function isUserMember(Project $project, int $userId): bool;

    /**
     * Search projects by name or description.
     */
    public function searchProjects(string $search): Collection;
}
