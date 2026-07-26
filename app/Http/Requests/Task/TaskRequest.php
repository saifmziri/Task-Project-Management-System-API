<?php

namespace App\Http\Requests\task;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class TaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task_name' => 'required|string|max:255',
            'project_id' => 'string',
            'status' => 'required|in:completed,pending,Cancel',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'nullable|date',
            'user_id' => 'exists:users,id',
        ];
    }
}
