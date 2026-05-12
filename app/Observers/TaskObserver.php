<?php

namespace App\Observers;

use App\Models\Activity;
use App\Models\Task;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        $this->logActivity($task, 'task_created', "created task '{$task->name}'");
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $trackedFields = [
            'stage' => 'moved to',
            'priority' => 'updated priority to',
        ];

        foreach ($trackedFields as $field => $label) {
            if ($task->wasChanged($field)) {
                $val = ucfirst($task->{$field});
                $this->logActivity($task, "task_{$field}_updated", "{$label} '{$val}'");
            }
        }
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
         $this->logActivity($task, 'task_deleted', "Deleted task '{$task->name}'");
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        //
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        //
    }

    protected function logActivity(Task $task, string $type, string $description): void
    {
        Activity::create([
            'user_id' => auth()->id(),
            'project_id' => $task->project_id,
            'activitable_id' => $task->id,
            'activitable_type' => Task::class,
            'type' => $type,
            'description' => $description,
            'properties' => ['task_name' => $task->name]
        ]);
    }
}
