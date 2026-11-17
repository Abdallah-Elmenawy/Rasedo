<?php

namespace App\Repositories\Reports\Contracts;

use Illuminate\Support\Collection;

interface ReportRepositoryInterface
{
    public function inventoryProducts(): Collection;

    public function customersByBalance(): Collection;

    public function suppliersByBalance(): Collection;

    public function profit(string $from, string $to): array;

    public function accountsByType(): Collection;

    public function ledger(mixed $accountId): array;
}
