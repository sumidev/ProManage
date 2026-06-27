<?php

namespace App\Contracts\ProjectRepository;

use App\Models\Project;

interface ProjectServiceInterface
{
    /**
     * Get formatted details for a project including Kanban task structure.
     */
    public function getProjectDetailsForKanban(Project $project, int $userId): array;

    /**
     * Handle the process of inviting a member to a project.
     */
    public function inviteMemberToProject(int $projectId, string $email, int $inviterId): array;
}
