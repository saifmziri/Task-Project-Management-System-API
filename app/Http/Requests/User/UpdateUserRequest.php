<?php

namespace App\Http\Requests\User;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

public function rules(): array
{
    // أخذ الـ ID من الـ Route بأي شكل كان
    $userId = $this->route('id') ?? $this->route('user') ?? $this->user()?->id;

    return [
        'full_name'    => ['sometimes', 'required', 'string', 'max:255'],
        'phone_number' => ['sometimes', 'required', 'string', 'max:20'],
        'email'        => [
            'sometimes',
            'required',
            'email',
            Rule::unique('users')->ignore($userId),
        ],
    ];
}
}