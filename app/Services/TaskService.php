<?php

namespace App\Services;

use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;

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

    public function updateTask(int $taskId, array $data)
    {
        return $this->taskRepository->updateTask($taskId, $data);
    }

    public function deleteTask(int $taskId)
    {
        return $this->taskRepository->deleteTask($taskId);
    }
}