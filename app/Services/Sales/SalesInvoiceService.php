<?php

namespace App\Services\Sales;

use App\Models\Inventory\InventoryMovement;
use App\Models\PublicSettings\CompanySetting;
use App\Models\Customers\Customer;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesReturn;
use App\Repositories\Customers\Contracts\CustomerRepositoryInterface;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Repositories\Sales\Contracts\SalesInvoiceRepositoryInterface;
use App\Repositories\Treasury\Contracts\TreasuryRepositoryInterface;
use App\Services\Accounting\AccountingService;
use App\Support\GridQuery;
use App\Support\InvoiceSerials;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class SalesInvoiceService
{
    public function __construct(
        private readonly SalesInvoiceRepositoryInterface $invoices,
        private readonly CustomerRepositoryInterface $customers,
        private readonly ProductRepositoryInterface $products,
        private readonly TreasuryRepositoryInterface $treasuries,
        private readonly AccountingService $accounting,
    ) {}

    public function index(?string $number, ?string $customerId, ?string $status): array
    {
        $query = $this->invoices->filtered($number, $customerId, $status);

        return [
            'invoices' => (clone $query)->latest()->paginate(15)->withQueryString(),
            'displayedTotal' => (clone $query)->sum('total'),
            'grandTotal' => $this->invoices->grandTotal(),
            'customers' => $this->customers->orderedByName(),
            'formCustomers' => $this->customers->activeOrdered(),
            'products' => $this->products->activeOrdered(),
            'invoice' => new SalesInvoice(['invoice_date' => now()->toDateString()]),
            'treasuries' => $this->treasuries->activeOrdered(),
            'filters' => [
                'customers' => GridQuery::related(SalesInvoice::query(), 'customer_id', Customer::class),
                'statuses' => GridQuery::distinct(SalesInvoice::query(), 'status', fn ($status) => invoice_status_label((string) $status)),
                'creators' => GridQuery::users(SalesInvoice::query()),
            ],
        ];
    }

    public function show(SalesInvoice $invoice): array
    {
        return [
            'invoice' => $this->invoices->loadShow($invoice),
            'treasuries' => $this->treasuries->active(),
        ];
    }

    public function editForm(SalesInvoice $invoice): array
    {
        if ($invoice->exists) {
            $invoice->load('items');
        }

        return [
            'invoice' => $invoice,
            'customers' => $this->customers->activeOrdered(),
            'products' => $this->products->activeOrdered(),
            'treasuries' => $this->treasuries->activeOrdered(),
        ];
    }

    public function save(array $data, ?SalesInvoice $invoice = null): SalesInvoice
    {
        return DB::transaction(function () use ($data, $invoice) {
            $subtotal = $tax = $itemDiscount = 0;
            foreach ($data['items'] as $item) {
                $line = ((float) $item['qty'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0);
                $subtotal += $line;
                $tax += (float) ($item['tax'] ?? 0);
                $itemDiscount += (float) ($item['discount'] ?? 0);
            }
            $headerDiscount = (float) ($data['discount'] ?? 0);
            $total = max($subtotal + $tax - $headerDiscount, 0);
            $payload = [
                'customer_id' => $data['customer_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'draft',
                'subtotal' => $subtotal + $itemDiscount,
                'discount' => $headerDiscount + $itemDiscount,
                'tax' => $tax,
                'total' => $total,
                'paid' => 0,
                'remaining' => $total,
                'notes' => $data['notes'] ?? null,
                'order_type' => \App\Support\ReceiptText::normalizeOrderType($data['order_type'] ?? null),
                'created_by' => auth()->id(),
            ];
            $lines = [];
            foreach ($data['items'] as $item) {
                $lines[] = [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'serials' => InvoiceSerials::normalize($item['serials'] ?? []),
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => ((float) $item['qty'] * (float) $item['unit_price']) - (float) ($item['discount'] ?? 0) + (float) ($item['tax'] ?? 0),
                ];
            }
            if (! $invoice) {
                $payload['number'] = CompanySetting::current()->nextNumber('sale');
                $invoice = $this->invoices->create($payload);
            } else {
                $invoice->update($payload);
            }

            return $this->invoices->replaceItems($invoice, $lines);
        });
    }

    public function confirm(SalesInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->load('items.product');
            InvoiceSerials::sell($invoice);
            $this->accounting->confirmSalesInvoice($invoice);
        });
    }

    public function confirmAndCollect(SalesInvoice $invoice, array $payment): void
    {
        DB::transaction(function () use ($invoice, $payment) {
            $invoice = $invoice->fresh('items.product');
            InvoiceSerials::sell($invoice);
            $this->accounting->confirmSalesInvoice($invoice);
            $this->collectIfPaid($invoice, $payment);
        });
    }

    public function collect(SalesInvoice $invoice, array $data): void
    {
        if (! in_array($invoice->status, ['confirmed', 'partial'], true) || $data['amount'] > (float) $invoice->remaining) {
            throw new RuntimeException('لا يمكن التحصيل بهذا المبلغ.');
        }
        $this->accounting->recordReceipt($data + [
            'party_type' => Customer::class,
            'party_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'method' => 'cash',
        ]);
    }

    public function cancel(SalesInvoice $invoice): void
    {
        if ($invoice->status !== 'draft') {
            throw new RuntimeException('يمكن إلغاء المسودة فقط.');
        }
        $invoice->update(['status' => 'cancelled']);
    }

    public function delete(SalesInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            if (SalesReturn::query()->where('sales_invoice_id', $invoice->id)->exists()) {
                throw new RuntimeException('لا يمكن حذف فاتورة لها مرتجعات.');
            }

            $invoice->load(['items.product', 'payments.treasury', 'customer']);

            if (in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
                foreach ($invoice->payments as $payment) {
                    if ($payment->treasury && $payment->treasury->current_balance < $payment->amount) {
                        throw new RuntimeException('لا يمكن حذف الفاتورة لأن رصيد الخزينة غير كافٍ للتراجع عن التحصيل.');
                    }
                    $payment->treasury?->decrement('current_balance', $payment->amount);
                    $invoice->customer?->increment('current_balance', $payment->amount);
                    $this->accounting->reversePostedJournal($payment->journal_id);
                    $payment->delete();
                }

                $invoice->customer?->decrement('current_balance', $invoice->total);

                foreach ($invoice->items as $item) {
                    if ($item->product?->type === 'product') {
                        $item->product->increment('current_stock', $item->qty);
                    }
                }

                InventoryMovement::query()
                    ->where('reference_type', SalesInvoice::class)
                    ->where('reference_id', $invoice->id)
                    ->delete();

                $this->accounting->reversePostedJournal($invoice->journal_id);
            }

            $invoice->delete();
        });
    }

    public function print(SalesInvoice $invoice): SalesInvoice
    {
        return $this->invoices->loadPrint($invoice);
    }

    protected function collectIfPaid(SalesInvoice $invoice, array $payment): void
    {
        if (($payment['payment_status'] ?? null) !== 'paid') {
            return;
        }
        $invoice->refresh();
        $amount = min((float) ($payment['paid_amount'] ?? 0), (float) $invoice->total);
        if ($amount <= 0) {
            return;
        }
        if (empty($payment['treasury_id'])) {
            throw new RuntimeException('اختر الخزينة لتحصيل المبلغ.');
        }
        $this->accounting->recordReceipt([
            'treasury_id' => $payment['treasury_id'],
            'amount' => $amount,
            'transacted_at' => $invoice->invoice_date?->toDateString() ?? now()->toDateString(),
            'party_type' => Customer::class,
            'party_id' => $invoice->customer_id,
            'invoice_id' => $invoice->id,
            'method' => 'cash',
            'notes' => 'تحصيل فاتورة مبيعات '.$invoice->number,
        ]);
    }
}
