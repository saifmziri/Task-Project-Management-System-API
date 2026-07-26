<?php

namespace App\Http\Requests\task;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ChangeTaskStatusRequest extends FormRequest
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
            'status' => 'required|in:completed,pending,Cancel',
        ];
    }
    public function messages(): array
    {
        return [
            'status.required' => 'The status field is required.',
            'status.in'       => 'The status must be either completed or pending or Cancel.',
        ];
    }
}
