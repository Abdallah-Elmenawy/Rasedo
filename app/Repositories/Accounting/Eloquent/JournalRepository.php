<?php

namespace App\Repositories\Accounting\Eloquent;

use App\Models\Accounting\Journal;
use App\Repositories\Accounting\Contracts\JournalRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class JournalRepository implements JournalRepositoryInterface
{
    public function paginateFiltered(?string $search, int $perPage = 15): LengthAwarePaginator
    {
        $query = Journal::with('creator');
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'journal_date' => ['column' => 'journal_date', 'type' => 'date'],
            'description' => ['column' => 'description', 'type' => 'text'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function loadDetail(Journal $journal): Journal
    {
        return $journal->load('lines.account', 'creator');
    }
}
