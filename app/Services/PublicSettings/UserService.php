<?php

namespace App\Services\PublicSettings;

use App\Models\AllUsers\User;
use App\Models\PublicSettings\ActivityLog;
use App\Models\PublicSettings\Role;
use App\Support\GridQuery;
use App\Repositories\PublicSettings\Contracts\PermissionRepositoryInterface;
use App\Repositories\PublicSettings\Contracts\RoleRepositoryInterface;
use App\Repositories\PublicSettings\Contracts\UserRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $users,
        private readonly RoleRepositoryInterface $roles,
        private readonly PermissionRepositoryInterface $permissions,
    ) {}

    public function page(): array
    {
        return [
            'users' => $this->users->paginateWithRole(),
            'roles' => $this->roles->ordered(),
            'filters' => [
                'names' => GridQuery::distinct(User::query(), 'name'),
                'roles' => GridQuery::related(User::query(), 'role_id', Role::class),
                'statuses' => GridQuery::labeled(User::query(), 'status', [
                    'active' => 'نشط',
                    'disabled' => 'معطّل',
                    'suspended' => 'موقوف',
                ]),
            ],
        ];
    }

    public function editor(User $user): array
    {
        return [
            'user' => $this->users->loadEditor($user),
            'roles' => $this->roles->ordered(),
            'permissions' => $this->permissions->groupedByModule(),
        ];
    }

    public function create(array $data, bool $twoFactor): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['two_factor_enabled'] = $twoFactor;
        $data['email_verified_at'] = now();
        $user = $this->users->create($data);
        ActivityLog::record('users', 'create', "إضافة مستخدم {$user->name}", $user);

        return $user;
    }

    public function update(User $user, array $data, bool $twoFactor, array $permissionIds): User
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $data['two_factor_enabled'] = $twoFactor;
        $user = $this->users->update($user, $data);
        $this->users->syncExtraPermissions($user, $permissionIds);
        ActivityLog::record('users', 'update', "تعديل مستخدم {$user->name}", $user);

        return $user;
    }

    public function updateProfile(User $user, array $data): User
    {
        return $this->users->update($user, $data);
    }

    public function replaceAvatar(User $user, UploadedFile $file): string
    {
        $dir = public_path('uploads/users');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        foreach (glob($dir.DIRECTORY_SEPARATOR.$user->id.'.*') ?: [] as $old) {
            if (is_file($old)) {
                unlink($old);
            }
        }
        $name = $user->id.'.'.strtolower($file->extension());
        $file->move($dir, $name);

        return 'uploads/users/'.$name;
    }

    public function clearAvatar(User $user): void
    {
        $this->deleteAvatarFile($user->avatar);
    }

    public function changePassword(User $user, string $password): void
    {
        $this->users->update($user, ['password' => Hash::make($password)]);
    }

    private function deleteAvatarFile(?string $path): void
    {
        if (! is_string($path) || $path === '' || str_contains($path, '..') || ! str_starts_with($path, 'uploads/users/')) {
            return;
        }
        $file = public_path($path);
        if (is_file($file)) {
            unlink($file);
        }
    }
}
