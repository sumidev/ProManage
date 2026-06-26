<?php

namespace App\Contracts;

interface TaskServiceInterface
{
    /**
     * Get tasks for a project.
     */
    public function getTasksForProject(int $projectId);

    /**
     * Create a new task.
     */
    public function createTask(array $data);

    /**
     * Get a task by ID.
     */
    public function getTaskById(int $taskId);

    /**
     * Update a task.
     */
    public function updateTask(int $taskId, array $data);

    /**
     * Delete a task.
     */
    public function deleteTask(int $taskId);
}