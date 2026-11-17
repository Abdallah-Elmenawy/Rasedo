<?php

namespace App\Services\Purchases;

use App\Models\Inventory\InventoryMovement;
use App\Models\PublicSettings\CompanySetting;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseReturn;
use App\Models\Suppliers\Supplier;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Repositories\Purchases\Contracts\PurchaseInvoiceRepositoryInterface;
use App\Repositories\Suppliers\Contracts\SupplierRepositoryInterface;
use App\Repositories\Treasury\Contracts\TreasuryRepositoryInterface;
use App\Services\Accounting\AccountingService;
use App\Support\GridQuery;
use App\Support\InvoiceSerials;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseInvoiceService
{
    public function __construct(
        private readonly PurchaseInvoiceRepositoryInterface $invoices,
        private readonly SupplierRepositoryInterface $suppliers,
        private readonly ProductRepositoryInterface $products,
        private readonly TreasuryRepositoryInterface $treasuries,
        private readonly AccountingService $accounting,
    ) {}

    public function index(?string $number, ?string $supplierId, ?string $status): array
    {
        $query = $this->invoices->filtered($number, $supplierId, $status);

        return [
            'invoices' => (clone $query)->latest()->paginate(15)->withQueryString(),
            'displayedTotal' => (clone $query)->sum('total'),
            'grandTotal' => $this->invoices->grandTotal(),
            'suppliers' => $this->suppliers->orderedByName(),
            'formSuppliers' => $this->suppliers->activeOrdered(),
            'products' => $this->products->activeOrdered(),
            'invoice' => new PurchaseInvoice(['invoice_date' => now()->toDateString()]),
            'treasuries' => $this->treasuries->activeOrdered(),
            'filters' => [
                'suppliers' => GridQuery::related(PurchaseInvoice::query(), 'supplier_id', Supplier::class),
                'statuses' => GridQuery::distinct(PurchaseInvoice::query(), 'status', fn ($status) => invoice_status_label((string) $status)),
                'creators' => GridQuery::users(PurchaseInvoice::query()),
            ],
        ];
    }

    public function show(PurchaseInvoice $invoice): array
    {
        return [
            'invoice' => $this->invoices->loadShow($invoice),
            'treasuries' => $this->treasuries->active(),
        ];
    }

    public function editForm(PurchaseInvoice $invoice): array
    {
        if ($invoice->exists) {
            $invoice->load('items');
        }

        return [
            'invoice' => $invoice,
            'suppliers' => $this->suppliers->activeOrdered(),
            'products' => $this->products->activeOrdered(),
            'treasuries' => $this->treasuries->activeOrdered(),
        ];
    }

    public function save(array $data, ?PurchaseInvoice $invoice = null): PurchaseInvoice
    {
        return DB::transaction(function () use ($data, $invoice) {
            $subtotal = $tax = $itemDiscount = 0;
            foreach ($data['items'] as $item) {
                $line = ((float) $item['qty'] * (float) $item['unit_cost']) - (float) ($item['discount'] ?? 0);
                $subtotal += $line;
                $tax += (float) ($item['tax'] ?? 0);
                $itemDiscount += (float) ($item['discount'] ?? 0);
            }
            $headerDiscount = (float) ($data['discount'] ?? 0);
            $total = max($subtotal + $tax - $headerDiscount, 0);
            $payload = [
                'supplier_id' => $data['supplier_id'],
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
                'created_by' => auth()->id(),
            ];
            $lines = [];
            foreach ($data['items'] as $item) {
                $lines[] = [
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'],
                    'serials' => InvoiceSerials::normalize($item['serials'] ?? []),
                    'unit_cost' => $item['unit_cost'],
                    'discount' => $item['discount'] ?? 0,
                    'tax' => $item['tax'] ?? 0,
                    'total' => ((float) $item['qty'] * (float) $item['unit_cost']) - (float) ($item['discount'] ?? 0) + (float) ($item['tax'] ?? 0),
                ];
            }
            if (! $invoice) {
                $payload['number'] = CompanySetting::current()->nextNumber('purchase');
                $invoice = $this->invoices->create($payload);
            } else {
                $invoice->update($payload);
            }

            $invoice = $this->invoices->replaceItems($invoice, $lines);
            $this->applyAttachment($invoice, $data);

            return $invoice->fresh();
        });
    }

    public function storeAttachment(PurchaseInvoice $invoice, UploadedFile $file): PurchaseInvoice
    {
        $dir = public_path('uploads/purchases');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->deleteAttachmentFiles($invoice);
        $name = $this->attachmentBasename($invoice).'.'.strtolower($file->extension());
        $file->move($dir, $name);
        $invoice->update(['attachment' => 'uploads/purchases/'.$name]);

        return $invoice->fresh();
    }

    public function clearAttachment(PurchaseInvoice $invoice): void
    {
        $this->deleteAttachmentFiles($invoice);
        if ($invoice->attachment) {
            $invoice->update(['attachment' => null]);
        }
    }

    public function confirm(PurchaseInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            $invoice->load('items.product');
            InvoiceSerials::receive($invoice);
            $this->accounting->confirmPurchaseInvoice($invoice);
        });
    }

    public function confirmAndPay(PurchaseInvoice $invoice, array $payment): void
    {
        DB::transaction(function () use ($invoice, $payment) {
            $invoice = $invoice->fresh('items.product');
            InvoiceSerials::receive($invoice);
            $this->accounting->confirmPurchaseInvoice($invoice);
            $this->payIfPaid($invoice, $payment);
        });
    }

    public function pay(PurchaseInvoice $invoice, array $data): void
    {
        $this->accounting->recordPayment($data + [
            'party_type' => Supplier::class,
            'party_id' => $invoice->supplier_id,
            'invoice_id' => $invoice->id,
            'method' => 'cash',
        ]);
    }

    public function cancel(PurchaseInvoice $invoice): void
    {
        if ($invoice->status !== 'draft') {
            throw new RuntimeException('يمكن إلغاء المسودة فقط.');
        }
        $invoice->update(['status' => 'cancelled']);
    }

    public function delete(PurchaseInvoice $invoice): void
    {
        DB::transaction(function () use ($invoice) {
            if (PurchaseReturn::query()->where('purchase_invoice_id', $invoice->id)->exists()) {
                throw new RuntimeException('لا يمكن حذف فاتورة لها مرتجعات.');
            }

            $invoice->load(['items.product', 'payments.treasury', 'supplier']);

            if (in_array($invoice->status, ['confirmed', 'partial', 'paid'], true)) {
                foreach ($invoice->payments as $payment) {
                    $payment->treasury?->increment('current_balance', $payment->amount);
                    $invoice->supplier?->increment('current_balance', $payment->amount);
                    $this->accounting->reversePostedJournal($payment->journal_id);
                    $payment->delete();
                }

                $invoice->supplier?->decrement('current_balance', $invoice->total);

                foreach ($invoice->items as $item) {
                    if ($item->product?->type === 'product') {
                        if ($item->product->current_stock < $item->qty) {
                            throw new RuntimeException("لا يمكن حذف الفاتورة لأن مخزون {$item->product->name} غير كافٍ للتراجع.");
                        }
                        $item->product->decrement('current_stock', $item->qty);
                    }
                }

                InventoryMovement::query()
                    ->where('reference_type', PurchaseInvoice::class)
                    ->where('reference_id', $invoice->id)
                    ->delete();

                $this->accounting->reversePostedJournal($invoice->journal_id);
            }

            $this->clearAttachment($invoice);
            $invoice->delete();
        });
    }

    public function print(PurchaseInvoice $invoice): PurchaseInvoice
    {
        return $this->invoices->loadPrint($invoice);
    }

    protected function payIfPaid(PurchaseInvoice $invoice, array $payment): void
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
            throw new RuntimeException('اختر الخزينة لصرف المبلغ.');
        }
        $this->accounting->recordPayment([
            'treasury_id' => $payment['treasury_id'],
            'amount' => $amount,
            'transacted_at' => $invoice->invoice_date?->toDateString() ?? now()->toDateString(),
            'party_type' => Supplier::class,
            'party_id' => $invoice->supplier_id,
            'invoice_id' => $invoice->id,
            'method' => 'cash',
            'notes' => 'صرف فاتورة مشتريات '.$invoice->number,
        ]);
    }

    private function applyAttachment(PurchaseInvoice $invoice, array $data): void
    {
        $file = $data['attachment'] ?? null;
        if ($file instanceof UploadedFile) {
            $this->storeAttachment($invoice, $file);

            return;
        }
        if (! empty($data['remove_attachment'])) {
            $this->clearAttachment($invoice);
        }
    }

    private function deleteAttachmentFiles(PurchaseInvoice $invoice): void
    {
        $dir = public_path('uploads/purchases');
        $base = $this->attachmentBasename($invoice);
        foreach (glob($dir.DIRECTORY_SEPARATOR.$base.'.*') ?: [] as $old) {
            if (is_file($old)) {
                unlink($old);
            }
        }

        $stored = (string) $invoice->attachment;
        if ($stored !== '' && ! str_contains($stored, '..') && str_starts_with($stored, 'uploads/purchases/')) {
            $full = public_path($stored);
            if (is_file($full)) {
                unlink($full);
            }
        }
    }

    private function attachmentBasename(PurchaseInvoice $invoice): string
    {
        $base = trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '-', (string) $invoice->number), '-');

        return $base !== '' ? $base : 'invoice-'.$invoice->id;
    }
}
