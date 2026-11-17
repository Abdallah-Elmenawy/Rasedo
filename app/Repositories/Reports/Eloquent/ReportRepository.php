<?php

namespace App\Repositories\Reports\Eloquent;

use App\Enums\InvoiceStatus;
use App\Enums\MoneyEntryType;
use App\Models\Accounting\Account;
use App\Models\Customers\Customer;
use App\Models\Accounting\JournalLine;
use App\Models\Expenses\MoneyEntry;
use App\Models\Products\Product;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Sales\SalesInvoice;
use App\Models\Suppliers\Supplier;
use App\Repositories\Reports\Contracts\ReportRepositoryInterface;
use Illuminate\Support\Collection;

class ReportRepository implements ReportRepositoryInterface
{
    public function inventoryProducts(): Collection
    {
        return Product::with('category')->where('type', 'product')->orderBy('name')->get();
    }

    public function customersByBalance(): Collection
    {
        return Customer::query()->orderByDesc('current_balance')->get();
    }

    public function suppliersByBalance(): Collection
    {
        return Supplier::query()->orderByDesc('current_balance')->get();
    }

    public function profit(string $from, string $to): array
    {
        $posted = InvoiceStatus::posted();

        return [
            'sales' => (float) SalesInvoice::query()->whereBetween('invoice_date', [$from, $to])->whereIn('status', $posted)->sum('total'),
            'purchases' => (float) PurchaseInvoice::query()->whereBetween('invoice_date', [$from, $to])->whereIn('status', $posted)->sum('total'),
            'expenses' => (float) MoneyEntry::query()->where('type', MoneyEntryType::Expense->value)->whereBetween('entry_date', [$from, $to])->sum('amount'),
            'revenues' => (float) MoneyEntry::query()->where('type', MoneyEntryType::Revenue->value)->whereBetween('entry_date', [$from, $to])->sum('amount'),
        ];
    }

    public function accountsByType(): Collection
    {
        return Account::query()->orderBy('code')->get()->groupBy('type');
    }

    public function ledger(mixed $accountId): array
    {
        $accounts = Account::query()->orderBy('code')->get();
        $account = $accountId ? Account::query()->find($accountId) : null;
        $lines = collect();
        if ($account) {
            $query = JournalLine::with('journal')->where('account_id', $account->id);
            if (request()->filled('journal_number')) {
                $query->whereHas('journal', fn ($q) => $q->where('number', 'like', '%'.request('journal_number').'%'));
            }
            if (request()->filled('description')) {
                $query->whereHas('journal', fn ($q) => $q->where('description', 'like', '%'.request('description').'%'));
            }
            if (request()->filled('journal_date')) {
                $query->whereHas('journal', fn ($q) => $q->whereDate('journal_date', request('journal_date')));
            }
            if (request('debit') !== null && request('debit') !== '') {
                $query->where('debit', request('debit'));
            }
            if (request('credit') !== null && request('credit') !== '') {
                $query->where('credit', request('credit'));
            }
            $lines = $query->latest()->paginate(15)->withQueryString();
        }

        return compact('accounts', 'account', 'lines');
    }
}
