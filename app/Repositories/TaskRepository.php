<?php

namespace App\Repositories;
use App\Contracts\TaskRepositoryInterface;
use App\Models\Task;

class TaskRepository implements TaskRepositoryInterface
{
    public function getTasksForProject(int $projectId)
    {
        return Task::where("project_id", $projectId)->get();
    }

    public function createTask(array $data) : Task
    {
        return Task::create($data);
    }

    public function getTaskById(int $taskId)
    {
        return Task::findOrFail($taskId);
    }

    public function updateTask(Task $task, array $data):bool
    {
        return $task->update($data);
    }

    public function deleteTask(Task $task):  bool
    {
        return $task->delete();
    }
}