<?php

namespace App\Repositories\Expenses\Eloquent;

use App\Models\Expenses\MoneyEntry;
use App\Repositories\Expenses\Contracts\MoneyEntryRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MoneyEntryRepository implements MoneyEntryRepositoryInterface
{
    public function paginateLatest(int $perPage = 15): LengthAwarePaginator
    {
        $query = MoneyEntry::with('category', 'treasury');
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'type' => ['column' => 'type', 'type' => 'select'],
            'money_category_id' => ['column' => 'money_category_id', 'type' => 'select'],
            'treasury_id' => ['column' => 'treasury_id', 'type' => 'select'],
            'amount' => ['column' => 'amount', 'type' => 'number'],
            'entry_date' => ['column' => 'entry_date', 'type' => 'date'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }

    public function betweenDates(string $from, string $to): Collection
    {
        return MoneyEntry::with('category', 'treasury')->whereBetween('entry_date', [$from, $to])->latest('entry_date')->get();
    }
}
