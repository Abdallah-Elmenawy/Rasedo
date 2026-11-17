<?php

namespace App\Services\Sales;

use App\Models\Inventory\InventoryMovement;
use App\Models\PublicSettings\CompanySetting;
use App\Models\Customers\Customer;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesReturn;
use App\Support\GridQuery;
use App\Repositories\Sales\Contracts\SalesInvoiceRepositoryInterface;
use App\Services\Accounting\AccountingService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesReturnService
{
    public function __construct(
        private readonly SalesInvoiceRepositoryInterface $invoices,
        private readonly AccountingService $accounting,
    ) {}

    public function form(?int $invoiceId = null): array
    {
        $invoices = $this->invoices->postedForReturn();

        return [
            'invoices' => $invoices,
            'invoice' => $invoiceId ? $invoices->firstWhere('id', $invoiceId) : null,
        ];
    }

    public function page(): array
    {
        $invoices = $this->invoices->postedForReturn();

        return [
            'returns' => $this->invoices->paginateReturns(),
            'invoices' => $invoices,
            'filters' => [
                'customers' => GridQuery::related(SalesReturn::query(), 'customer_id', Customer::class),
                'invoices' => GridQuery::related(SalesReturn::query(), 'sales_invoice_id', SalesInvoice::class, 'number'),
                'statuses' => GridQuery::distinct(SalesReturn::query(), 'status', fn ($status) => invoice_status_label((string) $status)),
                'creators' => GridQuery::users(SalesReturn::query()),
            ],
            'invoiceItemsJson' => $invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'items' => $invoice->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? '-',
                    'qty' => $item->qty,
                    'price' => $item->unit_price,
                ])->values(),
            ])->values(),
        ];
    }

    public function create(array $data): SalesReturn
    {
        $invoice = SalesInvoice::query()->findOrFail($data['sales_invoice_id']);

        return DB::transaction(function () use ($data, $invoice) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += (float) $item['qty'] * (float) $item['unit_price'];
            }
            $return = SalesReturn::query()->create([
                'number' => CompanySetting::current()->nextNumber('sale').'-R',
                'sales_invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'return_date' => $data['return_date'],
                'status' => 'draft',
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
            foreach ($data['items'] as $item) {
                $return->items()->create($item + ['total' => (float) $item['qty'] * (float) $item['unit_price']]);
            }

            return $return;
        });
    }

    public function update(SalesReturn $return, array $data): SalesReturn
    {
        if ($return->status !== 'draft') {
            throw new RuntimeException('لا يمكن تعديل مرتجع مؤكد.');
        }

        return DB::transaction(function () use ($return, $data) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += (float) $item['qty'] * (float) $item['unit_price'];
            }
            $return->update([
                'return_date' => $data['return_date'],
                'notes' => $data['notes'] ?? null,
                'total' => $total,
            ]);
            $return->items()->delete();
            foreach ($data['items'] as $item) {
                $return->items()->create($item + ['total' => (float) $item['qty'] * (float) $item['unit_price']]);
            }

            return $return->fresh();
        });
    }

    public function show(SalesReturn $return): SalesReturn
    {
        return $return->load('customer', 'invoice', 'items.product');
    }

    public function delete(SalesReturn $return): void
    {
        DB::transaction(function () use ($return) {
            if ($return->status === 'confirmed') {
                $return->load('items.product', 'customer');
                foreach ($return->items as $item) {
                    if ($item->product?->type === 'product') {
                        if ($item->product->current_stock < $item->qty) {
                            throw new RuntimeException("لا يمكن حذف المرتجع لأن مخزون {$item->product->name} غير كافٍ للتراجع.");
                        }
                        $item->product->decrement('current_stock', $item->qty);
                    }
                }
                InventoryMovement::query()
                    ->where('reference_type', SalesReturn::class)
                    ->where('reference_id', $return->id)
                    ->delete();
                $this->accounting->reversePostedJournal($return->journal_id);
                $return->customer?->increment('current_balance', $return->total);
            }
            $return->delete();
        });
    }

    public function confirm(SalesReturn $return): void
    {
        $this->accounting->confirmSalesReturn($return->load('items.product', 'customer'));
    }
}
