<?php

namespace App\Repositories\Accounting\Eloquent;

use App\Models\Accounting\Account;
use App\Models\Customers\Customer;
use App\Models\Suppliers\Supplier;
use App\Repositories\Accounting\Contracts\AccountRepositoryInterface;
use Illuminate\Support\Collection;

class AccountRepository implements AccountRepositoryInterface
{
    public function orderedWithParent(): Collection
    {
        return Account::query()->with('parent')->orderBy('code')->get();
    }

    public function activeOrdered(): Collection
    {
        return Account::query()->where('is_active', true)->orderBy('code')->get();
    }

    public function ordered(): Collection
    {
        return Account::query()->orderBy('code')->get();
    }

    public function groupedByType(): Collection
    {
        return Account::query()->orderBy('code')->get()->groupBy('type');
    }

    public function create(array $data): Account
    {
        return Account::query()->create($data);
    }

    public function update(Account $account, array $data): Account
    {
        $account->update($data);

        return $account;
    }

    public function delete(Account $account): void
    {
        $account->delete();
    }

    public function customersByBalance(): Collection
    {
        return Customer::query()->orderByDesc('current_balance')->get();
    }

    public function suppliersByBalance(): Collection
    {
        return Supplier::query()->orderByDesc('current_balance')->get();
    }
}
