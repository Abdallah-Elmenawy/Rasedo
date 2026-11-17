<?php

namespace App\Repositories\Expenses\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface MoneyEntryRepositoryInterface
{
    public function paginateLatest(int $perPage = 15): LengthAwarePaginator;

    public function betweenDates(string $from, string $to): Collection;
}
