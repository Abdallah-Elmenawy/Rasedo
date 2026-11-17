<?php

namespace App\Repositories\Treasury\Eloquent;

use App\Enums\InvoiceStatus;
use App\Models\Treasury\MoneyTransaction;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Sales\SalesInvoice;
use App\Models\Treasury\Treasury;
use App\Models\Treasury\TreasuryTransfer;
use App\Repositories\Treasury\Contracts\TreasuryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TreasuryRepository implements TreasuryRepositoryInterface
{
    public function allWithAccount(): Collection
    {
        return Treasury::with('account')->get();
    }

    public function active(): Collection
    {
        return Treasury::query()->where('is_active', true)->get();
    }

    public function activeOrdered(): Collection
    {
        return Treasury::query()->where('is_active', true)->orderBy('name')->get();
    }

    public function create(array $data): Treasury
    {
        return Treasury::query()->create($data);
    }

    public function recentReceipts(int $limit = 8): Collection
    {
        return MoneyTransaction::with('treasury', 'party')->where('type', 'receipt')->latest()->limit($limit)->get();
    }

    public function recentPayments(int $limit = 8): Collection
    {
        return MoneyTransaction::with('treasury', 'party')->where('type', 'payment')->latest()->limit($limit)->get();
    }

    public function recentTransfers(int $limit = 8): Collection
    {
        return TreasuryTransfer::with('fromTreasury', 'toTreasury')->latest()->limit($limit)->get();
    }

    public function paginateReceipts(int $perPage = 15): LengthAwarePaginator
    {
        return MoneyTransaction::with('treasury', 'party')->where('type', 'receipt')->latest()->paginate($perPage, ['*'], 'receipts_page')->withQueryString();
    }

    public function paginatePayments(int $perPage = 15): LengthAwarePaginator
    {
        return MoneyTransaction::with('treasury', 'party')->where('type', 'payment')->latest()->paginate($perPage, ['*'], 'payments_page')->withQueryString();
    }

    public function paginateTransfers(int $perPage = 15): LengthAwarePaginator
    {
        return TreasuryTransfer::with('fromTreasury', 'toTreasury')->latest()->paginate($perPage, ['*'], 'transfers_page')->withQueryString();
    }

    public function openSalesInvoices(): Collection
    {
        return SalesInvoice::query()->whereIn('status', InvoiceStatus::collectable())->get();
    }

    public function openPurchaseInvoices(): Collection
    {
        return PurchaseInvoice::query()->whereIn('status', InvoiceStatus::collectable())->get();
    }
}
