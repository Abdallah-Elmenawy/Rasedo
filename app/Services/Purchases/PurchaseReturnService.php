<?php

namespace App\Services\Purchases;

use App\Models\Inventory\InventoryMovement;
use App\Models\PublicSettings\CompanySetting;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseReturn;
use App\Models\Suppliers\Supplier;
use App\Support\GridQuery;
use App\Repositories\Purchases\Contracts\PurchaseInvoiceRepositoryInterface;
use App\Services\Accounting\AccountingService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseReturnService
{
    public function __construct(
        private readonly PurchaseInvoiceRepositoryInterface $invoices,
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
                'suppliers' => GridQuery::related(PurchaseReturn::query(), 'supplier_id', Supplier::class),
                'invoices' => GridQuery::related(PurchaseReturn::query(), 'purchase_invoice_id', PurchaseInvoice::class, 'number'),
                'statuses' => GridQuery::distinct(PurchaseReturn::query(), 'status', fn ($status) => invoice_status_label((string) $status)),
                'creators' => GridQuery::users(PurchaseReturn::query()),
            ],
            'invoiceItemsJson' => $invoices->map(fn ($invoice) => [
                'id' => $invoice->id,
                'items' => $invoice->items->map(fn ($item) => [
                    'product_id' => $item->product_id,
                    'name' => $item->product->name ?? '-',
                    'qty' => $item->qty,
                    'price' => $item->unit_cost,
                ])->values(),
            ])->values(),
        ];
    }

    public function create(array $data): PurchaseReturn
    {
        $invoice = PurchaseInvoice::query()->findOrFail($data['purchase_invoice_id']);

        return DB::transaction(function () use ($data, $invoice) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += (float) $item['qty'] * (float) $item['unit_cost'];
            }
            $return = PurchaseReturn::query()->create([
                'number' => CompanySetting::current()->nextNumber('purchase').'-R',
                'purchase_invoice_id' => $invoice->id,
                'supplier_id' => $invoice->supplier_id,
                'return_date' => $data['return_date'],
                'status' => 'draft',
                'total' => $total,
                'notes' => $data['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);
            foreach ($data['items'] as $item) {
                $return->items()->create($item + ['total' => (float) $item['qty'] * (float) $item['unit_cost']]);
            }

            return $return;
        });
    }

    public function update(PurchaseReturn $return, array $data): PurchaseReturn
    {
        if ($return->status !== 'draft') {
            throw new RuntimeException('لا يمكن تعديل مرتجع مؤكد.');
        }

        return DB::transaction(function () use ($return, $data) {
            $total = 0;
            foreach ($data['items'] as $item) {
                $total += (float) $item['qty'] * (float) $item['unit_cost'];
            }
            $return->update([
                'return_date' => $data['return_date'],
                'notes' => $data['notes'] ?? null,
                'total' => $total,
            ]);
            $return->items()->delete();
            foreach ($data['items'] as $item) {
                $return->items()->create($item + ['total' => (float) $item['qty'] * (float) $item['unit_cost']]);
            }

            return $return->fresh();
        });
    }

    public function show(PurchaseReturn $return): PurchaseReturn
    {
        return $return->load('supplier', 'invoice', 'items.product');
    }

    public function delete(PurchaseReturn $return): void
    {
        DB::transaction(function () use ($return) {
            if ($return->status === 'confirmed') {
                $return->load('items.product', 'supplier');
                foreach ($return->items as $item) {
                    if ($item->product?->type === 'product') {
                        $item->product->increment('current_stock', $item->qty);
                    }
                }
                InventoryMovement::query()
                    ->where('reference_type', PurchaseReturn::class)
                    ->where('reference_id', $return->id)
                    ->delete();
                $this->accounting->reversePostedJournal($return->journal_id);
                $return->supplier?->increment('current_balance', $return->total);
            }
            $return->delete();
        });
    }

    public function confirm(PurchaseReturn $return): void
    {
        $this->accounting->confirmPurchaseReturn($return->load('items.product', 'supplier'));
    }
}
