<?php

namespace App\Services\Accounting;

use App\Models\Accounting\Account;
use App\Models\Expenses\MoneyCategory;
use App\Models\PublicSettings\CompanySetting;
use App\Models\Treasury\Treasury;
use App\Repositories\Accounting\Contracts\AccountRepositoryInterface;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function __construct(private readonly AccountRepositoryInterface $accounts) {}

    public function chart(): \Illuminate\Support\Collection
    {
        return $this->accounts->orderedWithParent();
    }

    public function active(): \Illuminate\Support\Collection
    {
        return $this->accounts->activeOrdered();
    }

    public function all(): \Illuminate\Support\Collection
    {
        return $this->accounts->ordered();
    }

    public function create(array $data): Account
    {
        return $this->accounts->create($data + [
            'current_balance' => $data['opening_balance'] ?? 0,
            'is_active' => true,
        ]);
    }

    public function update(Account $account, array $data): Account
    {
        return $this->accounts->update($account, $data);
    }

    public function delete(Account $account): void
    {
        if ($account->children()->exists()) {
            throw ValidationException::withMessages([
                'account' => 'احذف الحسابات الفرعية أولاً، أو انقلها لحساب أب آخر.',
            ]);
        }

        if ($account->lines()->exists()) {
            throw ValidationException::withMessages([
                'account' => 'لا يمكن حذف الحساب لأن عليه قيود محاسبية.',
            ]);
        }

        $settings = CompanySetting::query()->first();
        $linkedToSettings = $settings && collect([
            $settings->sales_account_id,
            $settings->purchases_account_id,
            $settings->inventory_account_id,
            $settings->cogs_account_id,
            $settings->customers_account_id,
            $settings->suppliers_account_id,
            $settings->tax_account_id,
            $settings->sales_discount_account_id,
        ])->contains(fn ($id) => (int) $id === (int) $account->id);

        if ($linkedToSettings || Treasury::query()->where('account_id', $account->id)->exists() || MoneyCategory::query()->where('account_id', $account->id)->exists()) {
            throw ValidationException::withMessages([
                'account' => 'لا يمكن حذف الحساب لأنه مربوط بإعدادات الشركة أو الخزينة أو التصنيفات.',
            ]);
        }

        $this->accounts->delete($account);
    }

    public function receivables(): \Illuminate\Support\Collection
    {
        return $this->accounts->customersByBalance();
    }

    public function payables(): \Illuminate\Support\Collection
    {
        return $this->accounts->suppliersByBalance();
    }
}
