<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'required|string|in:task,project',
            'id'   => 'required|integer'
        ]);

        $modelClass = $request->type === 'task'
            ? \App\Models\Task::class
            : \App\Models\Project::class;

        $model = $modelClass::findOrFail($request->id);

        $comments = $model->comments()
            ->whereNull('parent_id')
            ->with([
                'user:id,first_name,last_name,profile_pic',
                'attachments',
                'replies.user:id,first_name,last_name,profile_pic',
                'replies.attachments'
            ])
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $comments->map(fn($comment) => $this->formatComment($comment))
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'description'      => 'nullable|string|required_without:attachment',
            'attachment'       => 'nullable|file|max:10240|mimes:jpg,jpeg,png,webp,pdf,doc,docx,txt,xlsx,csv,zip',
            'commentable_type' => 'required|string|in:task,project',
            'commentable_id'   => 'required|integer',
            'parent_id'        => 'nullable|exists:comments,id'
        ]);

        $modelClass = $validated['commentable_type'] === 'task'
            ? \App\Models\Task::class
            : \App\Models\Project::class;

        $model = $modelClass::findOrFail($validated['commentable_id']);

        $comment = $model->comments()->create([
            'description' => $validated['description'] ?? '',
            'parent_id'   => $validated['parent_id'] ?? null,
            'user_id'     => $request->user()->id,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');

            $path = $file->store('attachments', 'public');
            $comment->attachments()->create([
                'file_name'   => $file->getClientOriginalName(),
                'file_path'   => $path,
                'mime_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
        }

        $comment->load([
            'user:id,first_name,last_name,email,profile_pic',
            'attachments'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'data'    => $this->formatComment($comment)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    private function formatComment($comment)
    {
        return [
            'id'          => $comment->id,
            'description' => $comment->description,
            'parent_id'   => $comment->parent_id,
            'created_at'  => $comment->created_at,

            'attachments' => $comment->relationLoaded('attachments') && $comment->attachments->isNotEmpty()
                ? $comment->attachments->map(function ($file) {
                    return [
                        'id'   => $file->id,
                        'name' => $file->file_name,
                        'mime' => $file->mime_type,
                        'size' => $file->file_size,
                        'url'  => Storage::disk('public')->url($file->file_path),
                    ];
                })->all()
                : [],

            'user' => [
                'id'     => $comment->user->id,
                'name'   => $comment->user->first_name . ' ' . $comment->user->last_name,
                'avatar' => $comment->user->profile_pic
            ],

            'replies' => $comment->relationLoaded('replies')
                ? $comment->replies->map(fn($reply) => $this->formatComment($reply))->values()->all()
                : [],
        ];
    }
}
