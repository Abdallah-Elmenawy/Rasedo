<?php

namespace App\Repositories\Sales\Contracts;

use App\Models\Sales\SalesInvoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface SalesInvoiceRepositoryInterface
{
    public function filtered(?string $number, ?string $customerId, ?string $status): Builder;

    public function grandTotal(): float;

    public function create(array $data): SalesInvoice;

    public function replaceItems(SalesInvoice $invoice, array $items): SalesInvoice;

    public function loadShow(SalesInvoice $invoice): SalesInvoice;

    public function loadPrint(SalesInvoice $invoice): SalesInvoice;

    public function postedForReturn(): Collection;

    public function postedBetween(string $from, string $to): Collection;

    public function latest(int $limit): Collection;

    public function sumPosted(string $column = 'total'): float;

    public function countPosted(): int;

    public function sumPostedOnDate(string $date): float;

    public function paginateReturns(int $perPage = 15): LengthAwarePaginator;
}
