<?php

namespace App\Repositories\Sales\Eloquent;

use App\Enums\InvoiceStatus;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesReturn;
use App\Repositories\Sales\Contracts\SalesInvoiceRepositoryInterface;
use App\Support\GridQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalesInvoiceRepository implements SalesInvoiceRepositoryInterface
{
    public function filtered(?string $number, ?string $customerId, ?string $status): Builder
    {
        $query = SalesInvoice::with('customer', 'creator');
        if ($number && ! request()->filled('number')) {
            $query->where('number', 'like', '%'.$number.'%');
        }
        if ($customerId && ! request()->filled('customer_id')) {
            $query->where('customer_id', $customerId);
        }
        if ($status && ! request()->filled('status')) {
            $query->where('status', $status);
        }
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'customer_id' => ['column' => 'customer_id', 'type' => 'select'],
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
        return (float) SalesInvoice::query()->where('status', '!=', InvoiceStatus::Cancelled->value)->sum('total');
    }

    public function create(array $data): SalesInvoice
    {
        return SalesInvoice::query()->create($data);
    }

    public function replaceItems(SalesInvoice $invoice, array $items): SalesInvoice
    {
        $invoice->items()->delete();
        foreach ($items as $item) {
            $invoice->items()->create($item);
        }

        return $invoice;
    }

    public function loadShow(SalesInvoice $invoice): SalesInvoice
    {
        return $invoice->load(['customer', 'items.product.category', 'payments.treasury', 'creator']);
    }

    public function loadPrint(SalesInvoice $invoice): SalesInvoice
    {
        return $invoice->load(['customer', 'items.product.category', 'payments.treasury', 'creator']);
    }

    public function postedForReturn(): Collection
    {
        return SalesInvoice::with('customer', 'items.product')
            ->whereIn('status', InvoiceStatus::posted())
            ->latest()
            ->get();
    }

    public function postedBetween(string $from, string $to): Collection
    {
        return SalesInvoice::with('customer')
            ->whereBetween('invoice_date', [$from, $to])
            ->whereIn('status', InvoiceStatus::posted())
            ->latest('invoice_date')
            ->get();
    }

    public function latest(int $limit): Collection
    {
        return SalesInvoice::with('customer')->latest()->limit($limit)->get();
    }

    public function sumPosted(string $column = 'total'): float
    {
        return (float) SalesInvoice::query()->whereIn('status', InvoiceStatus::posted())->sum($column);
    }

    public function countPosted(): int
    {
        return SalesInvoice::query()->whereIn('status', InvoiceStatus::posted())->count();
    }

    public function sumPostedOnDate(string $date): float
    {
        return (float) SalesInvoice::query()->whereDate('invoice_date', $date)->whereIn('status', InvoiceStatus::posted())->sum('total');
    }

    public function paginateReturns(int $perPage = 15): LengthAwarePaginator
    {
        $query = SalesReturn::with('customer', 'invoice', 'creator');
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'customer_id' => ['column' => 'customer_id', 'type' => 'select'],
            'sales_invoice_id' => ['column' => 'sales_invoice_id', 'type' => 'select'],
            'return_date' => ['column' => 'return_date', 'type' => 'date'],
            'total' => ['column' => 'total', 'type' => 'number'],
            'status' => ['column' => 'status', 'type' => 'select'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        return $query->latest()->paginate($perPage)->withQueryString();
    }
}
