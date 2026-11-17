<?php

namespace App\Http\Requests\Admin\PublicSettings;

use App\Models\AllUsers\User;
use App\Models\PublicSettings\Role;
use App\Support\PermissionCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $id = $user?->id;

        return [
            'name' => ['required', 'string', 'max:150'],
            'username' => ['nullable', 'string', 'max:50', Rule::unique('users', 'username')->ignore($id)],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:6'],
            'role_id' => ['required', 'exists:roles,id', function ($attribute, $value, $fail) use ($user) {
                $role = Role::query()->find($value);
                if ((int) $value !== (int) $user?->role_id && ! PermissionCatalog::canAssignRole($this->user(), $role)) {
                    $fail('لا يمكنك منح دور صلاحياته أعلى من صلاحياتك.');
                }
                if ($user?->isAdmin() && $role?->slug !== 'admin' && $this->isLastAdmin($user)) {
                    $fail('لا يمكن تغيير دور آخر مدير للنظام.');
                }
            }],
            'status' => ['required', 'in:active,disabled,suspended', function ($attribute, $value, $fail) use ($user) {
                if ($value !== 'active' && $user?->id === $this->user()?->id) {
                    $fail('لا يمكنك إيقاف حسابك الحالي.');
                }
            }],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'remove_avatar' => ['nullable', 'boolean'],
        ];
    }

    private function isLastAdmin(User $user): bool
    {
        return ! User::query()
            ->whereKeyNot($user->id)
            ->where('status', 'active')
            ->whereHas('role', fn ($query) => $query->where('slug', 'admin'))
            ->exists();
    }
}
