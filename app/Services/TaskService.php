<?php

namespace App\Services;

use App\Contracts\TaskRepository\TaskRepositoryInterface;
use App\Contracts\TaskRepository\TaskServiceInterface;
use App\Models\Task;

class TaskService implements TaskServiceInterface
{
    protected $taskRepository;

    public function __construct(TaskRepositoryInterface $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function getTasksForProject(int $projectId)
    {
        return $this->taskRepository->getTasksForProject($projectId);
    }

    public function createTask(array $data)
    {
        return $this->taskRepository->createTask($data);
    }

    public function getTaskById(int $taskId)
    {
        return $this->taskRepository->getTaskById($taskId);
    }

    public function updateTask(Task $task, array $data)
    {
        return $this->taskRepository->updateTask($task, $data);
    }

    public function deleteTask(Task $task)
    {
        return $this->taskRepository->deleteTask($task);
    }
}