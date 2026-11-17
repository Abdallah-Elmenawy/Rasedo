<?php

namespace App\Http\Requests\Admin\PublicSettings;

use App\Models\PublicSettings\Role;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:50', 'unique:users,username'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id', function ($attribute, $value, $fail) {
                if (! PermissionCatalog::canAssignRole($this->user(), Role::query()->find($value))) {
                    $fail('لا يمكنك منح دور صلاحياته أعلى من صلاحياتك.');
                }
            }],
            'status' => ['required', 'in:active,disabled,suspended'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
        ];
    }
}
