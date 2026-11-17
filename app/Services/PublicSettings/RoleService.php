<?php

namespace App\Services\PublicSettings;

use App\Models\PublicSettings\ActivityLog;
use App\Models\PublicSettings\Permission;
use App\Models\PublicSettings\Role;
use App\Repositories\PublicSettings\Contracts\PermissionRepositoryInterface;
use App\Repositories\PublicSettings\Contracts\RoleRepositoryInterface;
use App\Support\GridQuery;
use App\Support\PermissionCatalog;
use Illuminate\Support\Str;
use RuntimeException;

class RoleService
{
    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly PermissionRepositoryInterface $permissions,
    ) {}

    public function page(): array
    {
        PermissionCatalog::sync();
        $roles = $this->roles->listWithPermissions();
        $filters = [
            'names' => $roles->pluck('name', 'name')->filter()->all(),
        ];
        $roles = GridQuery::applyCollection($roles, [
            'name' => ['key' => 'name', 'type' => 'select'],
            'description' => ['key' => 'description', 'type' => 'text'],
            'users_count' => ['key' => 'users_count', 'type' => 'number'],
        ]);
        if (request()->filled('permission')) {
            $needle = (string) request('permission');
            $roles = $roles->filter(fn ($role) => $role->permissions->contains(
                fn ($permission) => mb_stripos($permission->name, $needle) !== false
            ))->values();
        }

        return [
            'roles' => paginate_collection($roles),
            'filters' => $filters,
            'permissions' => $this->groupedPermissions(),
            'modules' => PermissionCatalog::modules(),
        ];
    }

    public function editor(Role $role): array
    {
        PermissionCatalog::sync();

        return [
            'role' => $role->load('permissions'),
            'permissions' => $this->groupedPermissions(),
            'modules' => PermissionCatalog::modules(),
        ];
    }

    public function create(array $data, array $permissionIds, array $permissionNames = []): Role
    {
        $base = Str::slug($data['name']) ?: 'role';
        $slug = $base;
        $i = 1;
        while ($this->roles->slugExists($slug)) {
            $slug = $base.'-'.$i++;
        }
        $role = $this->roles->create($data + ['slug' => $slug]);
        $this->roles->syncPermissions($role, $permissionIds);
        $this->renamePermissions($permissionNames);
        ActivityLog::record('users', 'create', "إضافة دور {$role->name}", $role);

        return $role;
    }

    public function update(Role $role, array $data, array $permissionIds, array $permissionNames = []): Role
    {
        $role = $this->roles->update($role, $data);
        $ids = $role->slug === 'admin' ? $this->permissions->allIds()->all() : $permissionIds;
        $this->roles->syncPermissions($role, $ids);
        $this->renamePermissions($permissionNames);
        ActivityLog::record('users', 'update', "تعديل دور {$role->name}", $role);

        return $role;
    }

    public function delete(Role $role): void
    {
        if ($role->slug === 'admin') {
            throw new RuntimeException('لا يمكن حذف دور مدير النظام.');
        }
        if ($role->users()->exists()) {
            throw new RuntimeException('لا يمكن حذف الدور لأنه مرتبط بمستخدمين.');
        }
        $name = $role->name;
        $this->roles->delete($role);
        ActivityLog::record('users', 'delete', "حذف دور {$name}");
    }

    protected function renamePermissions(array $names): void
    {
        foreach ($names as $id => $name) {
            $name = trim((string) $name);
            if ($name === '') {
                continue;
            }
            Permission::query()->whereKey($id)->update(['name' => mb_substr($name, 0, 150)]);
        }
    }

    protected function groupedPermissions()
    {
        $grouped = $this->permissions->all()->groupBy('module');
        $order = ['view' => 1, 'create' => 2, 'update' => 3, 'delete' => 4];

        return collect(PermissionCatalog::modules())
            ->map(fn ($label, $module) => ($grouped->get($module) ?? collect())->sortBy(fn ($permission) => $order[$permission->action] ?? 9)->values())
            ->filter(fn ($items) => $items->isNotEmpty());
    }
}
