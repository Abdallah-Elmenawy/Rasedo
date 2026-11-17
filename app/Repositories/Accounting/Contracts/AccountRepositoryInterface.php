<?php

namespace App\Repositories\Accounting\Contracts;

use App\Models\Accounting\Account;
use Illuminate\Support\Collection;

interface AccountRepositoryInterface
{
    public function orderedWithParent(): Collection;

    public function activeOrdered(): Collection;

    public function ordered(): Collection;

    public function groupedByType(): Collection;

    public function create(array $data): Account;

    public function update(Account $account, array $data): Account;

    public function delete(Account $account): void;

    public function customersByBalance(): Collection;

    public function suppliersByBalance(): Collection;
}
