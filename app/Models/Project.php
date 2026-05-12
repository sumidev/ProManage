<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Enums\ProjectStage;
use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'type',
        'stages',
        'status',
        'deadline',
        'created_by',
        'user_id'
    ];

    protected $casts = [
        'stages' => 'array',
        'status' => ProjectStatus::class,
        'deadline' => 'date',
        'created_at' => 'datetime',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members() {
        return $this->belongsToMany(User::class, 'project_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    public function pendingTasks()
    {
        return $this->tasks()->where('status', 'pending');
    }

    public function inProgressTasks()
    {
        return $this->tasks()->where('status', 'in_progress');
    }

    public function completedTasks()
    {
        return $this->tasks()->where('status', 'completed');
    }

    public function comments()
    {
        return $this->morphMany(Comment::class, 'commentable');
    }
}
