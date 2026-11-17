<?php

namespace App\Repositories\Treasury\Contracts;

use App\Models\Treasury\Treasury;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface TreasuryRepositoryInterface
{
    public function allWithAccount(): Collection;

    public function active(): Collection;

    public function activeOrdered(): Collection;

    public function create(array $data): Treasury;

    public function recentReceipts(int $limit = 8): Collection;

    public function recentPayments(int $limit = 8): Collection;

    public function recentTransfers(int $limit = 8): Collection;

    public function paginateReceipts(int $perPage = 15): LengthAwarePaginator;

    public function paginatePayments(int $perPage = 15): LengthAwarePaginator;

    public function paginateTransfers(int $perPage = 15): LengthAwarePaginator;

    public function openSalesInvoices(): Collection;

    public function openPurchaseInvoices(): Collection;
}
