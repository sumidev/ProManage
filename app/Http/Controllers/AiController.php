<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Project;
use App\Models\Task;
use Carbon\Carbon;

class AiController extends Controller
{
    public function generateProject(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1500'
        ]);

        $user = $request->user();
        $userPrompt = $request->prompt;

        // The exact schema matching YOUR database structure
        $systemPrompt = "
        You are an elite AI assistant for a Project Management SaaS.
        Convert the user's input into structured project and task data.
        Return ONLY valid JSON. Do not include markdown formatting like ```json.

        Rules:
        - type (Project) MUST be one of: 'software','mobile_app','website','design', 'marketing'
        - priority MUST be one of: 'low', 'medium', 'high', 'critical'
        - type (Task) MUST be one of: 'task', 'bug', 'feature', 'story'
        - Assume today is " . Carbon::now()->toDateString() . " for relative dates.

        Expected JSON Format:
        {
            \"project_name\": \"Extracted or Generated Name\",
            \"project_description\": \"Short summary of the project\",
            \"type\": \"website\",
            \"deadline\": \"YYYY-MM-DD\" (or null if not mentioned),
            \"tasks\": [
                {
                    \"name\": \"Task name\",
                    \"description\": \"Brief details about the task\",
                    \"priority\": \"medium\",
                    \"type\": \"task\",
                    \"due_date\": \"YYYY-MM-DD\" (or null)
                }
            ]
        }
        
        User Input: " . $userPrompt;

        try {
            // Hitting Gemini 1.5 Flash API
            // Using Gemini 3.5 Flash (Stable)
            $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent?key=' . env('GEMINI_API_KEY');

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post($url, [
                'contents' => [
                    ['parts' => [['text' => $systemPrompt]]]
                ]
            ]);

            $result = $response->json();
            $aiText = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (!$aiText) {
                return response()->json(['error' => 'AI failed to generate response'], 500);
            }

            $aiText = str_replace(['```json', '```'], '', trim($aiText));
            $parsedData = json_decode(trim($aiText), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json(['error' => 'AI returned invalid data format', 'raw' => $aiText], 500);
            }

            // === DATABASE INSERTION ===

            // 1. Create Project
            $project = Project::create([
                'name' => $parsedData['project_name'],
                'description' => $parsedData['project_description'] ?? null,
                'type' => $parsedData['type'] ?? 'software',
                'stages' => [
                    "backlog",
                    "todo",
                    "in_progress",
                    "review",
                    "done"
                ],
                'deadline' => $parsedData['deadline'] ?? null,
                'user_id' => $user->id,
                'status' => 'active'
            ]);

            // 2. Create Tasks (Matching your Tasks Schema)
            if (!empty($parsedData['tasks'])) {
                $taskData = [];
                foreach ($parsedData['tasks'] as $index => $task) {
                    $taskData[] = [
                        'project_id' => $project->id,
                        'name' => $task['name'], // Updated to match your schema
                        'description' => $task['description'] ?? null,
                        'priority' => $task['priority'] ?? 'medium',
                        'type' => $task['type'] ?? 'task',
                        'status' => 'todo',
                        'stage' => 'todo', // Required by your index
                        'order' => $index,
                        'due_date' => $task['due_date'] ?? null,
                        'assigned_by' => $user->id, // REQUIRED by your schema!
                        'assigned_to' => null, // AI tasks are unassigned by default
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                Task::insert($taskData);
            }

            // Load tasks to return complete data to frontend
            $project->load('tasks');

            return response()->json([
                'message' => 'Magic! Project and tasks generated.',
                'project' => $project
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
