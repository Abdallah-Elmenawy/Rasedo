<?php

namespace App\Repositories\PublicSettings\Contracts;

use App\Models\AllUsers\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface UserRepositoryInterface
{
    public function paginateWithRole(int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): User;

    public function update(User $user, array $data): User;

    public function syncExtraPermissions(User $user, array $permissionIds): void;

    public function loadEditor(User $user): User;
}
