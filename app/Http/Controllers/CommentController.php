<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Dom\Comment;
use Illuminate\Http\Request;

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
                'replies'
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
            'description'      => 'required|string',
            'commentable_type' => 'required|string|in:task,project', // Frontend se 'task' ya 'project' aayega
            'commentable_id'   => 'required|integer',
            'parent_id'        => 'nullable|exists:comments,id' // Agar reply hai, toh parent ID
        ]);

        // 2. Map string to actual Laravel Model Class
        $modelClass = $validated['commentable_type'] === 'task'
            ? Task::class
            : Project::class;

        // 3. Check karo ki Task ya Project actual me exist karta hai ya nahi
        $model = $modelClass::findOrFail($validated['commentable_id']);

        // 4. Create the Comment (Morph relation use karke)
        $comment = $model->comments()->create([
            'description' => $validated['description'],
            'parent_id'   => $validated['parent_id'],
            'user_id'     => $request->user()->id,
            'parent_id'   => $request->parent_id,
        ]);

        // 5. Frontend (React) ko user ki details chahiye hogi UI me avatar dikhane ke liye
        $comment->load('user:id,first_name,last_name,email,profile_pic');

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

    private function formatComment($comment, $isReply = false)
    {
        $data = [
            'id'          => $comment->id,
            'description' => $comment->description,
            'parent_id'   => $comment->parent_id,
            'created_at'  => $comment->created_at,
            'user' => [
                'id'     => $comment->user->id,
                'name'   => $comment->user->first_name . ' ' . $comment->user->last_name,
                'avatar' => $comment->user->profile_pic
            ]
        ];

        if (!$isReply) {
            $data['replies'] = $comment->relationLoaded('replies')
                                ? $this->flattenReplies($comment->replies)
                                : [];
        }

        return $data;
    }

    // 2. The Flattener Helper
    private function flattenReplies($replies)
    {
        $flatArray = [];
        foreach ($replies as $reply) {
            $flatArray[] = $this->formatComment($reply, true);
            if ($reply->relationLoaded('replies') && $reply->replies->isNotEmpty()) {
                $flatArray = array_merge($flatArray, $this->flattenReplies($reply->replies));
            }
        }
        return $flatArray;
    }
}
