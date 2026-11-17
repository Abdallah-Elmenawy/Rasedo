<?php

namespace App\Services\Sales;

use App\Enums\InvoiceStatus;
use App\Enums\MoneyEntryType;
use App\Models\PublicSettings\CompanySetting;
use App\Models\Customers\Customer;
use App\Models\Expenses\MoneyEntry;
use App\Models\Products\Product;
use App\Models\Products\ProductSerial;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesReturn;
use App\Models\Treasury\MoneyTransaction;
use App\Models\Treasury\Treasury;
use App\Repositories\Customers\Contracts\CustomerRepositoryInterface;
use App\Repositories\Products\Contracts\ProductCategoryRepositoryInterface;
use App\Repositories\Products\Contracts\ProductRepositoryInterface;
use App\Repositories\Treasury\Contracts\TreasuryRepositoryInterface;
use App\Services\Accounting\AccountingService;
use App\Support\ReceiptText;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PosService
{
    public function __construct(
        private readonly ProductRepositoryInterface $products,
        private readonly ProductCategoryRepositoryInterface $categories,
        private readonly CustomerRepositoryInterface $customers,
        private readonly TreasuryRepositoryInterface $treasuries,
        private readonly AccountingService $accounting,
    ) {}

    public function page(): array
    {
        $products = $this->products->activeForPos();
        $shift = session('pos_shift');
        $shift = is_array($shift) ? $shift : null;
        $treasury = $shift ? Treasury::query()->find($shift['treasury_id']) : null;

        return [
            'categories' => $this->categories->orderedByName(),
            'customers' => $this->customers->activeOrdered(),
            'treasuries' => $this->treasuries->activeOrdered(),
            'shift' => $shift,
            'shiftPreview' => $shift ? $this->shiftFigures($shift) : null,
            'stats' => $this->stats($treasury),
            'posProducts' => $products->map(fn (Product $product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'scan_code' => $product->scan_code,
                'track_serial' => (bool) $product->track_serial,
                'pieces_per_box' => (int) ($product->pieces_per_box ?: 0),
                'unit' => $product->unit,
                'price' => (float) $product->sale_price,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => (float) $product->current_stock,
                'type' => $product->type,
                'category_id' => $product->category_id,
                'letter' => mb_substr($product->name, 0, 1),
            ]),
        ];
    }

    public function closeShift(array $shift, float $actualCash): array
    {
        $figures = $this->shiftFigures($shift);
        $expected = $figures['opening_balance'] + $figures['cash'];
        $figures['actual_cash'] = $actualCash;
        $figures['expected_cash'] = $expected;
        $figures['difference'] = round($actualCash - $expected, 2);

        return $figures;
    }

    public function checkout(array $data, array $shift): SalesInvoice
    {
        return DB::transaction(function () use ($data, $shift) {
            $subtotal = $tax = 0;
            $lines = [];
            foreach ($data['items'] as $row) {
                $product = Product::query()->whereKey($row['product_id'])->lockForUpdate()->first();
                if (! $product || ! $product->is_active) {
                    throw new RuntimeException('أحد المنتجات غير متاح للبيع.');
                }
                $qty = (float) $row['qty'];
                $price = (float) $product->sale_price;
                if ($product->type === 'product' && $product->current_stock < $qty) {
                    throw new RuntimeException("الكمية غير كافية للمنتج {$product->name}.");
                }
                $line = $qty * $price;
                $lineTax = round($line * ((float) $product->tax_rate / 100), 2);
                $subtotal += $line;
                $tax += $lineTax;
                $lines[] = [
                    'product_id' => $product->id,
                    'qty' => $qty,
                    'unit_price' => $price,
                    'discount' => 0,
                    'tax' => $lineTax,
                    'total' => $line + $lineTax,
                    'serials' => array_values(array_filter($row['serials'] ?? [])),
                ];
            }
            $headerDiscount = (float) ($data['discount'] ?? 0);
            if ($headerDiscount > $subtotal + $tax) {
                throw new RuntimeException('قيمة الخصم أكبر من إجمالي الطلب.');
            }
            $total = max($subtotal + $tax - $headerDiscount, 0);
            $asDraft = ($data['submit_action'] ?? 'pay') === 'draft';
            $invoice = SalesInvoice::query()->create([
                'number' => CompanySetting::current()->nextNumber('sale'),
                'customer_id' => $data['customer_id'],
                'invoice_date' => now()->toDateString(),
                'status' => 'draft',
                'subtotal' => $subtotal,
                'discount' => $headerDiscount,
                'tax' => $tax,
                'total' => $total,
                'paid' => 0,
                'remaining' => $total,
                'notes' => 'نقطة بيع — '.ReceiptText::orderLabel($data['order_type']),
                'order_type' => ReceiptText::normalizeOrderType($data['order_type']),
                'created_by' => auth()->id(),
            ]);
            foreach ($lines as $line) {
                $codes = $line['serials'];
                unset($line['serials']);
                $stored = [];
                foreach ($codes as $serialCode) {
                    $serial = ProductSerial::query()
                        ->where('product_id', $line['product_id'])
                        ->where('serial', $serialCode)
                        ->lockForUpdate()
                        ->first();
                    if (! $serial || $serial->status !== 'in_stock') {
                        throw new RuntimeException('السيريال غير متاح: '.$serialCode);
                    }
                    $stored[] = ['code' => $serialCode, 'kind' => $serial->kind === 'box' ? 'box' : 'piece'];
                    if (! $asDraft) {
                        $serial->update(['status' => 'sold', 'sales_invoice_id' => $invoice->id]);
                    }
                }
                $line['serials'] = $stored;
                $invoice->items()->create($line);
            }
            if ($asDraft) {
                return $invoice->fresh();
            }
            $this->accounting->confirmSalesInvoice($invoice->fresh('items.product'));
            $paidAmount = $data['payment_status'] === 'unpaid'
                ? 0.0
                : min((float) ($data['paid_amount'] ?? 0), $total);
            if ($paidAmount > 0) {
                $treasuryId = $shift['treasury_id'] ?? ($data['treasury_id'] ?? null);
                if (empty($treasuryId)) {
                    throw new RuntimeException('اختر الخزينة لتحصيل المبلغ.');
                }
                $this->accounting->recordReceipt([
                    'treasury_id' => $treasuryId,
                    'amount' => $paidAmount,
                    'transacted_at' => now()->toDateString(),
                    'party_type' => Customer::class,
                    'party_id' => $invoice->customer_id,
                    'invoice_id' => $invoice->id,
                    'method' => $data['payment_method'] ?? 'cash',
                    'notes' => 'تحصيل نقطة البيع '.$invoice->number,
                ]);
            }

            return $invoice->fresh();
        });
    }

    private function stats(?Treasury $treasury): array
    {
        $today = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $monthStart = now()->startOfMonth()->toDateString();
        $posted = InvoiceStatus::posted();

        $sumOn = function (string $date) use ($posted): float {
            return (float) SalesInvoice::query()
                ->whereDate('invoice_date', $date)
                ->whereIn('status', $posted)
                ->sum('total');
        };
        $countOn = function (string $date) use ($posted): int {
            return SalesInvoice::query()
                ->whereDate('invoice_date', $date)
                ->whereIn('status', $posted)
                ->count();
        };
        $returnsOn = function (string $from, string $to): float {
            return (float) SalesReturn::query()
                ->whereBetween('return_date', [$from, $to])
                ->where('status', 'confirmed')
                ->sum('total');
        };

        $todaySales = $sumOn($today);
        $yesterdaySales = $sumOn($yesterday);
        $todayCount = $countOn($today);
        $yesterdayCount = $countOn($yesterday);
        $monthSales = (float) SalesInvoice::query()
            ->whereBetween('invoice_date', [$monthStart, $today])
            ->whereIn('status', $posted)
            ->sum('total');
        $monthReturns = $returnsOn($monthStart, $today);
        $todayPurchases = (float) PurchaseInvoice::query()
            ->whereDate('invoice_date', $today)
            ->whereIn('status', $posted)
            ->sum('total');
        $todayExpenses = (float) MoneyEntry::query()
            ->where('type', MoneyEntryType::Expense->value)
            ->whereDate('entry_date', $today)
            ->sum('amount');
        $todayRevenues = (float) MoneyEntry::query()
            ->where('type', MoneyEntryType::Revenue->value)
            ->whereDate('entry_date', $today)
            ->sum('amount');

        return [
            'today_count' => $todayCount,
            'today_count_delta' => $this->delta($todayCount, $yesterdayCount),
            'today_sales' => $todaySales,
            'today_average' => $todayCount > 0 ? round($todaySales / $todayCount, 2) : 0,
            'today_net' => max($todaySales - $returnsOn($today, $today), 0),
            'today_profit' => $todaySales + $todayRevenues - $todayPurchases - $todayExpenses,
            'today_sales_delta' => $this->delta($todaySales, $yesterdaySales),
            'month_sales' => $monthSales,
            'net_sales' => max($monthSales - $monthReturns, 0),
            'drawer' => (float) ($treasury->current_balance ?? 0),
        ];
    }

    private function shiftFigures(array $shift): array
    {
        $invoices = SalesInvoice::query()
            ->where('created_by', $shift['user_id'])
            ->where('created_at', '>=', $shift['opened_at'])
            ->where('notes', 'like', 'نقطة بيع%')
            ->whereIn('status', InvoiceStatus::posted())
            ->get();
        $ids = $invoices->pluck('id');
        $payments = $ids->isEmpty()
            ? collect()
            : MoneyTransaction::query()
                ->where('invoice_type', SalesInvoice::class)
                ->whereIn('invoice_id', $ids)
                ->get();

        return [
            'treasury_name' => $shift['treasury_name'] ?? '',
            'opened_at' => $shift['opened_at'],
            'opening_balance' => (float) ($shift['opening_balance'] ?? 0),
            'invoices' => $invoices->count(),
            'sales' => (float) $invoices->sum('total'),
            'cash' => (float) $payments->where('method', 'cash')->sum('amount'),
            'card' => (float) $payments->where('method', '!=', 'cash')->sum('amount'),
        ];
    }

    private function delta(float|int $current, float|int $previous): float
    {
        if ((float) $previous == 0.0) {
            return (float) $current > 0 ? 100.0 : 0.0;
        }

        return round((((float) $current - (float) $previous) / (float) $previous) * 100, 1);
    }
}
