<?php

namespace App\Repositories\Purchases\Eloquent;

use App\Enums\InvoiceStatus;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseReturn;
use App\Repositories\Purchases\Contracts\PurchaseInvoiceRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PurchaseInvoiceRepository implements PurchaseInvoiceRepositoryInterface
{
    public function filtered(?string $number, ?string $supplierId, ?string $status): Builder
    {
        $query = PurchaseInvoice::with('supplier', 'creator');
        if ($number && ! request()->filled('number')) {
            $query->where('number', 'like', '%'.$number.'%');
        }
        if ($supplierId && ! request()->filled('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }
        if ($status && ! request()->filled('status')) {
            $query->where('status', $status);
        }
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'supplier_id' => ['column' => 'supplier_id', 'type' => 'select'],
            'invoice_date' => ['column' => 'invoice_date', 'type' => 'date'],
            'status' => ['column' => 'status', 'type' => 'select'],
            'total' => ['column' => 'total', 'type' => 'number'],
            'remaining' => ['column' => 'remaining', 'type' => 'number'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        return $query;
    }

    public function grandTotal(): float
    {
        return (float) PurchaseInvoice::query()->where('status', '!=', InvoiceStatus::Cancelled->value)->sum('total');
    }

    public function create(array $data): PurchaseInvoice
    {
        return PurchaseInvoice::query()->create($data);
    }

    public function replaceItems(PurchaseInvoice $invoice, array $items): PurchaseInvoice
    {
        $invoice->items()->delete();
        foreach ($items as $item) {
            $invoice->items()->create($item);
        }

        return $invoice;
    }

    public function loadShow(PurchaseInvoice $invoice): PurchaseInvoice
    {
        return $invoice->load(['supplier', 'items.product.category', 'payments.treasury']);
    }

    public function loadPrint(PurchaseInvoice $invoice): PurchaseInvoice
    {
        return $invoice->load(['supplier', 'items.product.category', 'payments.treasury', 'creator']);
    }

    public function postedForReturn(): Collection
    {
        return PurchaseInvoice::with('supplier', 'items.product')
            ->whereIn('status', InvoiceStatus::posted())
            ->latest()
            ->get();
    }

    public function postedBetween(string $from, string $to): Collection
    {
        return PurchaseInvoice::with('supplier')
            ->whereBetween('invoice_date', [$from, $to])
            ->whereIn('status', InvoiceStatus::posted())
            ->latest('invoice_date')
            ->get();
    }

    public function sumPosted(string $column = 'total'): float
    {
        return (float) PurchaseInvoice::query()->whereIn('status', InvoiceStatus::posted())->sum($column);
    }

    public function countPosted(): int
    {
        return PurchaseInvoice::query()->whereIn('status', InvoiceStatus::posted())->count();
    }

    public function sumPostedOnDate(string $date): float
    {
        return (float) PurchaseInvoice::query()->whereDate('invoice_date', $date)->whereIn('status', InvoiceStatus::posted())->sum('total');
    }

    public function paginateReturns(int $perPage = 15)
    {
        $query = PurchaseReturn::with('supplier', 'invoice', 'creator');
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'supplier_id' => ['column' => 'supplier_id', 'type' => 'select'],
            'purchase_invoice_id' => ['column' => 'purchase_invoice_id', 'type' => 'select'],
            'return_date' => ['column' => 'return_date', 'type' => 'date'],
            'total' => ['column' => 'total', 'type' => 'number'],
            'status' => ['column' => 'status', 'type' => 'select'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }
}
