<?php

namespace App\Services\Expenses;

use App\Models\Expenses\MoneyCategory;
use App\Models\Expenses\MoneyEntry;
use App\Models\Treasury\Treasury;
use App\Support\GridQuery;
use App\Repositories\Expenses\Contracts\MoneyCategoryRepositoryInterface;
use App\Repositories\Expenses\Contracts\MoneyEntryRepositoryInterface;
use App\Repositories\Treasury\Contracts\TreasuryRepositoryInterface;
use App\Services\Accounting\AccountingService;

class ExpenseService
{
    public function __construct(
        private readonly MoneyEntryRepositoryInterface $entries,
        private readonly MoneyCategoryRepositoryInterface $categories,
        private readonly TreasuryRepositoryInterface $treasuries,
        private readonly AccountingService $accounting,
    ) {}

    public function page(): array
    {
        return [
            'entries' => $this->entries->paginateLatest(),
            'categories' => $this->categories->orderedByName(),
            'treasuries' => $this->treasuries->active(),
            'filters' => [
                'types' => GridQuery::labeled(MoneyEntry::query(), 'type', ['expense' => 'مصروف', 'revenue' => 'إيراد']),
                'categories' => GridQuery::related(MoneyEntry::query(), 'money_category_id', MoneyCategory::class),
                'treasuries' => GridQuery::related(MoneyEntry::query(), 'treasury_id', Treasury::class),
            ],
        ];
    }

    public function formData(MoneyEntry $entry): array
    {
        return [
            'entry' => $entry,
            'categories' => $this->categories->orderedByName(),
            'treasuries' => $this->treasuries->active(),
        ];
    }

    public function create(array $data): void
    {
        $this->accounting->recordMoneyEntry($data);
    }

    public function update(MoneyEntry $entry, array $data): void
    {
        $this->accounting->updateMoneyEntry($entry, $data);
    }

    public function delete(MoneyEntry $entry): void
    {
        $this->accounting->deleteMoneyEntry($entry);
    }

    public function createCategory(array $data): MoneyCategory
    {
        return $this->categories->create($data);
    }
}
