<?php

namespace App\Http\Requests\Admin\PublicSettings;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'permission_names' => ['nullable', 'array'],
            'permission_names.*' => ['nullable', 'string', 'max:150'],
        ];
    }
}
