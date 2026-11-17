<?php

namespace App\Repositories\PublicSettings\Eloquent;

use App\Models\PublicSettings\Permission;
use App\Repositories\PublicSettings\Contracts\PermissionRepositoryInterface;
use Illuminate\Support\Collection;

class PermissionRepository implements PermissionRepositoryInterface
{
    public function groupedByModule(): Collection
    {
        return Permission::query()->orderBy('module')->orderBy('name')->get()->groupBy('module');
    }

    public function allIds(): Collection
    {
        return Permission::query()->pluck('id');
    }

    public function all(): Collection
    {
        return Permission::query()->get();
    }
}
