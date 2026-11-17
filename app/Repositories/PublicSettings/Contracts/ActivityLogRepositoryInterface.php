<?php

namespace App\Repositories\PublicSettings\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ActivityLogRepositoryInterface
{
    public function paginateLatest(int $perPage = 15): LengthAwarePaginator;
}
