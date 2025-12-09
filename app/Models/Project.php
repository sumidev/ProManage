<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'stage',
        'status',
        'deadline',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
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
