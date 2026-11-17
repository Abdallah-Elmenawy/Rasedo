<?php

namespace App\Repositories\Accounting\Contracts;

use App\Models\Accounting\Journal;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface JournalRepositoryInterface
{
    public function paginateFiltered(?string $search, int $perPage = 15): LengthAwarePaginator;

    public function loadDetail(Journal $journal): Journal;
}
