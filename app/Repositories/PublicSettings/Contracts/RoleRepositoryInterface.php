<?php

namespace App\Repositories\PublicSettings\Contracts;

use App\Models\PublicSettings\Role;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function listWithPermissions(): Collection;

    public function ordered(): Collection;

    public function slugExists(string $slug): bool;

    public function create(array $data): Role;

    public function update(Role $role, array $data): Role;

    public function syncPermissions(Role $role, array $permissionIds): void;

    public function delete(Role $role): void;
}
