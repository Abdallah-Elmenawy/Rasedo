<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Journal;
use App\Repositories\Accounting\Contracts\JournalRepositoryInterface;
use RuntimeException;

class JournalEntryService
{
    public function __construct(
        private readonly JournalRepositoryInterface $journals,
        private readonly AccountingService $accounting,
    ) {}

    public function paginate(?string $search)
    {
        return $this->journals->paginateFiltered($search);
    }

    public function post(array $data): Journal
    {
        $lines = collect($data['lines'])
            ->filter(fn ($line) => ! empty($line['account_id']) && (((float) ($line['debit'] ?? 0)) + ((float) ($line['credit'] ?? 0)) > 0))
            ->values()
            ->all();

        if (count($lines) < 2) {
            throw new RuntimeException('أدخل سطرين على الأقل.');
        }

        return $this->accounting->postJournal($data['description'], $lines, null, $data['journal_date']);
    }

    public function detail(Journal $journal): Journal
    {
        return $this->journals->loadDetail($journal);
    }
}
