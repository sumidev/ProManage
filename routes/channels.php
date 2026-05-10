<?php

use App\Models\Project;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['prefix' => 'api', 'middleware' => ['auth:sanctum']]);

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return Project::where('id', $projectId)
        ->where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereHas('members', fn($q) => $q->where('users.id', $user->id));
        })->exists();
});