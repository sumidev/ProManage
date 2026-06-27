<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if ($this->route('project')) {
            return $this->route('project')->user_id === $this->user()->id;
        }

        if ($this->route('task')) {
            return $this->route('task')->project->user_id === $this->user()->id;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'nullable|in:task,bug,feature,story',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'nullable|date',
            'stage' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }
}
