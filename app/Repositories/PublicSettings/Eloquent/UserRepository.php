<?php

namespace App\Repositories\PublicSettings\Eloquent;

use App\Models\AllUsers\User;
use App\Repositories\PublicSettings\Contracts\UserRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserRepository implements UserRepositoryInterface
{
    public function paginateWithRole(int $perPage = 15): LengthAwarePaginator
    {
        $query = User::with('role');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'email' => ['column' => 'email', 'type' => 'text'],
            'role_id' => ['column' => 'role_id', 'type' => 'select'],
            'status' => ['column' => 'status', 'type' => 'select'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user;
    }

    public function syncPermissionOverrides(User $user, array $grantedIds, array $revokedIds): void
    {
        $rows = array_fill_keys($grantedIds, ['granted' => true]) + array_fill_keys($revokedIds, ['granted' => false]);
        $user->permissionOverrides()->sync($rows);
        $user->unsetRelation('permissionOverrides');
    }

    public function loadEditor(User $user): User
    {
        return $user->load(['role.permissions', 'permissionOverrides']);
    }
}
