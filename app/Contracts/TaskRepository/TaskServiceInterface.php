<?php

namespace App\Contracts\TaskRepository;

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
    public function updateTask(\App\Models\Task $task, array $data);

    /**
     * Delete a task.
     */
    public function deleteTask(\App\Models\Task $task);
}