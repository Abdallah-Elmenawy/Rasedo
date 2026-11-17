<?php

namespace App\Repositories\PublicSettings\Eloquent;

use App\Models\PublicSettings\Role;
use App\Repositories\PublicSettings\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function listWithPermissions(): Collection
    {
        return Role::withCount('users')->with('permissions')->latest()->get();
    }

    public function ordered(): Collection
    {
        return Role::query()->orderBy('name')->get();
    }

    public function slugExists(string $slug): bool
    {
        return Role::query()->where('slug', $slug)->exists();
    }

    public function create(array $data): Role
    {
        return Role::query()->create($data);
    }

    public function update(Role $role, array $data): Role
    {
        $role->update($data);

        return $role;
    }

    public function syncPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
    }

    public function delete(Role $role): void
    {
        $role->permissions()->detach();
        $role->delete();
    }
}
