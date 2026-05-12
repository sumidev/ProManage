<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $fillable = [
        'user_id',
        'project_id',
        'activitable_id',
        'activitable_type',
        'type',
        'description',
        'properties',
    ];

    // Properties ko automatically array/object mein convert karne ke liye
    protected $casts = [
        'properties' => 'array',
    ];

    /**
     * Get the parent activitable model (Task, Project, etc.).
     */
    public function activitable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Kis user ne activity ki
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kis project ki activity hai
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}

