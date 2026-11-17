<?php

namespace App\Repositories\Inventory\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InventoryRepositoryInterface
{
    public function recentMovements(int $limit = 20): Collection;

    public function paginateMovements(int $perPage = 15): LengthAwarePaginator;
}
