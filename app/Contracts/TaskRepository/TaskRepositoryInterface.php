<?php

namespace App\Contracts;

use App\Models\Task;

interface TaskRepositoryInterface
{
    /**
     * Get tasks for a project.
     */
    public function getTasksForProject(int $projectId);

    /**
     * Create a new task.
     */
    public function createTask(array $data): Task;

    /**
     * Get a task by ID.
     */
    public function getTaskById(int $taskId);

    /**
     * Update a task.
     */
    public function updateTask(Task $task, array $data): bool;

    /**
     * Delete a task.
     */
    public function deleteTask(Task $task): bool;
}
