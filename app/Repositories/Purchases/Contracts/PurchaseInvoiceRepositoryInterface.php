<?php

namespace App\Repositories\Purchases\Contracts;

use App\Models\Purchases\PurchaseInvoice;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface PurchaseInvoiceRepositoryInterface
{
    public function filtered(?string $number, ?string $supplierId, ?string $status): Builder;

    public function grandTotal(): float;

    public function create(array $data): PurchaseInvoice;

    public function replaceItems(PurchaseInvoice $invoice, array $items): PurchaseInvoice;

    public function loadShow(PurchaseInvoice $invoice): PurchaseInvoice;

    public function loadPrint(PurchaseInvoice $invoice): PurchaseInvoice;

    public function postedForReturn(): Collection;

    public function postedBetween(string $from, string $to): Collection;

    public function sumPosted(string $column = 'total'): float;

    public function countPosted(): int;

    public function sumPostedOnDate(string $date): float;

    public function paginateReturns(int $perPage = 15);
}
