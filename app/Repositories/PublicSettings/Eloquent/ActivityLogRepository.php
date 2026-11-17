<?php

namespace App\Repositories\PublicSettings\Eloquent;

use App\Models\PublicSettings\ActivityLog;
use App\Repositories\PublicSettings\Contracts\ActivityLogRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ActivityLogRepository implements ActivityLogRepositoryInterface
{
    public function paginateLatest(int $perPage = 15): LengthAwarePaginator
    {
        $query = ActivityLog::with('user');
        GridQuery::apply($query, [
            'description' => ['column' => 'description', 'type' => 'text'],
            'module' => ['column' => 'module', 'type' => 'select'],
            'action' => ['column' => 'action', 'type' => 'select'],
            'user_id' => ['column' => 'user_id', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }
}
