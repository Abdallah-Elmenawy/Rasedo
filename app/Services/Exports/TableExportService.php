<?php

namespace App\Services\Exports;

use App\Enums\InvoiceStatus;
use App\Models\Accounting\Account;
use App\Models\Accounting\Journal;
use App\Models\Accounting\JournalLine;
use App\Models\AllUsers\User;
use App\Models\Customers\Customer;
use App\Models\Expenses\MoneyEntry;
use App\Models\Inventory\InventoryMovement;
use App\Models\Products\Product;
use App\Models\Products\ProductCategory;
use App\Models\PublicSettings\ActivityLog;
use App\Models\PublicSettings\Role;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Purchases\PurchaseReturn;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesReturn;
use App\Models\Suppliers\Supplier;
use App\Models\Treasury\MoneyTransaction;
use App\Models\Treasury\TreasuryTransfer;
use App\Services\Reports\ReportService;
use App\Support\GridQuery;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class TableExportService
{
    public function __construct(private readonly ReportService $reports) {}

    public function allowed(string $sheet): bool
    {
        $permission = $this->permissions()[$sheet] ?? null;

        return $permission !== null && can($permission);
    }

    /**
     * @return array{0: string, 1: array<int, string>, 2: Collection}
     */
    public function build(string $sheet): array
    {
        return match ($sheet) {
            'product-serials' => $this->productSerials(),
            'products' => $this->products(),
            'categories' => $this->categories(),
            'inventory' => $this->inventory(),
            'inventory-movements' => $this->inventoryMovements(),
            'sales' => $this->sales(),
            'sales-returns' => $this->salesReturns(),
            'purchases' => $this->purchases(),
            'purchase-returns' => $this->purchaseReturns(),
            'customers' => $this->customers(),
            'customer-accounts' => $this->customerAccounts(),
            'customer-invoices' => $this->partyInvoices(Customer::class, 'customer', 'فواتير العميل'),
            'customer-payments' => $this->partyPayments(Customer::class, 'customer', 'مدفوعات العميل'),
            'suppliers' => $this->suppliers(),
            'supplier-accounts' => $this->supplierAccounts(),
            'supplier-invoices' => $this->partyInvoices(Supplier::class, 'supplier', 'فواتير المورد'),
            'supplier-payments' => $this->partyPayments(Supplier::class, 'supplier', 'مدفوعات المورد'),
            'expenses' => $this->expenses(),
            'treasury-receipts' => $this->treasuryMoves('receipt', 'تحصيلات الخزينة', 'العميل'),
            'treasury-payments' => $this->treasuryMoves('payment', 'صرف الخزينة', 'المورد'),
            'treasury-transfers' => $this->treasuryTransfers(),
            'treasury-activity' => $this->treasuryActivity(),
            'accounts' => $this->accounts(),
            'receivables' => $this->balances(Customer::class, 'الحسابات المدينة', 'العميل'),
            'payables' => $this->balances(Supplier::class, 'الحسابات الدائنة', 'المورد'),
            'journals' => $this->journals(),
            'journal-lines' => $this->journalLines(),
            'sales-invoice-items' => $this->invoiceItems(SalesInvoice::class, 'sale', 'بنود فاتورة المبيعات', 'unit_price'),
            'purchase-invoice-items' => $this->invoiceItems(PurchaseInvoice::class, 'purchase', 'بنود فاتورة المشتريات', 'unit_cost'),
            'sales-return-items' => $this->returnItems(SalesReturn::class, 'sales_return', 'بنود مرتجع المبيعات', 'unit_price'),
            'purchase-return-items' => $this->returnItems(PurchaseReturn::class, 'purchase_return', 'بنود مرتجع المشتريات', 'unit_cost'),
            'users' => $this->users(),
            'roles' => $this->roles(),
            'logs' => $this->logs(),
            'reports-sales' => $this->reportSheet(fn () => $this->reports->sales(...$this->reports->period(request('from'), request('to')))),
            'reports-purchases' => $this->reportSheet(fn () => $this->reports->purchases(...$this->reports->period(request('from'), request('to')))),
            'reports-inventory' => $this->reportSheet(fn () => $this->reports->inventory()),
            'reports-customers' => $this->reportSheet(fn () => $this->reports->customers()),
            'reports-suppliers' => $this->reportSheet(fn () => $this->reports->suppliers()),
            'reports-expenses' => $this->reportSheet(fn () => $this->reports->expenses(...$this->reports->period(request('from'), request('to')))),
            'reports-profit' => $this->profit(),
            'reports-financial' => $this->financial(),
            'reports-ledger' => $this->ledger(),
            default => throw new InvalidArgumentException('Unknown sheet.'),
        };
    }

    private function permissions(): array
    {
        return [
            'product-serials' => 'products.view',
            'products' => 'products.view',
            'categories' => 'products.view',
            'inventory' => 'inventory.view',
            'inventory-movements' => 'inventory.view',
            'sales' => 'sales.view',
            'sales-returns' => 'sales.view',
            'purchases' => 'purchases.view',
            'purchase-returns' => 'purchases.view',
            'customers' => 'customers.view',
            'customer-accounts' => 'customers.view',
            'customer-invoices' => 'customers.view',
            'customer-payments' => 'customers.view',
            'suppliers' => 'suppliers.view',
            'supplier-accounts' => 'suppliers.view',
            'supplier-invoices' => 'suppliers.view',
            'supplier-payments' => 'suppliers.view',
            'expenses' => 'expenses.view',
            'treasury-receipts' => 'treasury.view',
            'treasury-payments' => 'treasury.view',
            'treasury-transfers' => 'treasury.view',
            'treasury-activity' => 'treasury.view',
            'accounts' => 'accounting.view',
            'receivables' => 'accounting.view',
            'payables' => 'accounting.view',
            'journals' => 'accounting.view',
            'journal-lines' => 'accounting.view',
            'sales-invoice-items' => 'sales.view',
            'purchase-invoice-items' => 'purchases.view',
            'sales-return-items' => 'sales.view',
            'purchase-return-items' => 'purchases.view',
            'users' => 'users.view',
            'roles' => 'users.view',
            'logs' => 'users.view',
            'reports-sales' => 'reports.view',
            'reports-purchases' => 'reports.view',
            'reports-inventory' => 'reports.view',
            'reports-customers' => 'reports.view',
            'reports-suppliers' => 'reports.view',
            'reports-expenses' => 'reports.view',
            'reports-profit' => 'reports.view',
            'reports-financial' => 'reports.view',
            'reports-ledger' => 'reports.view',
        ];
    }

    private function sheet(string $title, array $headings, Collection $rows): array
    {
        $numbered = $rows->values()->map(fn (array $row, int $index) => array_merge([$index + 1], $row));

        return [$title, array_merge(['#'], $headings), $numbered];
    }

    private function money(mixed $value): float
    {
        return round((float) $value, 2);
    }

    private function qty(mixed $value): float
    {
        return round((float) $value, 3);
    }

    private function day(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return $value ? (string) $value : '';
    }

    private function stamp(mixed $value): string
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i');
        }

        return $value ? (string) $value : '';
    }

    private function stockLabel(Product $product): string
    {
        if ($product->type !== 'product') {
            return '—';
        }
        if ($product->current_stock <= 0) {
            return 'نفد';
        }

        return $product->isLowStock() ? 'منخفض' : 'متوفر';
    }

    private function productSerials(): array
    {
        $product = Product::query()->with('serials')->findOrFail(request('product'));
        $rows = $product->serials->map(fn ($serial) => [
            $serial->serial,
            $serial->batch_no ?: '-',
            $serial->status === 'in_stock' ? 'متاح' : 'مباع',
        ]);

        return $this->sheet('سيريالات '.$product->name, ['السيريال', 'التشغيلة', 'الحالة'], $rows);
    }

    private function products(): array
    {
        $query = Product::with('category');
        GridQuery::apply($query, [
            'sku' => ['column' => 'sku', 'type' => 'text'],
            'name' => ['column' => 'name', 'type' => 'select'],
            'category_id' => ['column' => 'category_id', 'type' => 'select'],
            'type' => ['column' => 'type', 'type' => 'select'],
            'cost_price' => ['column' => 'cost_price', 'type' => 'number'],
            'sale_price' => ['column' => 'sale_price', 'type' => 'number'],
            'current_stock' => ['column' => 'current_stock', 'type' => 'number'],
        ]);
        $query->when(request('stock_status') === 'out', fn ($q) => $q->where('type', 'product')->where('current_stock', '<=', 0))
            ->when(request('stock_status') === 'low', fn ($q) => $q->where('type', 'product')->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock'))
            ->when(request('stock_status') === 'ok', fn ($q) => $q->where('type', 'product')->whereColumn('current_stock', '>', 'min_stock'));

        $rows = $query->latest()->get()->map(fn (Product $product) => [
            $product->sku,
            $product->scan_code ?: '—',
            $product->name,
            $product->category->name ?? '-',
            $product->type === 'service' ? 'خدمة' : 'منتج',
            $this->money($product->cost_price),
            $this->money($product->sale_price),
            $this->qty($product->current_stock),
            $this->stockLabel($product),
        ]);

        return $this->sheet('المنتجات', ['SKU', 'الباركود', 'الاسم', 'التصنيف', 'النوع', 'التكلفة', 'البيع', 'المخزون', 'الحالة'], $rows);
    }

    private function categories(): array
    {
        $query = ProductCategory::query()->withCount('products');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
        ]);
        if (request()->filled('products_count')) {
            $query->having('products_count', '=', request('products_count'));
        }

        $rows = $query->latest()->get()->map(fn (ProductCategory $category) => [
            $category->name,
            (int) $category->products_count,
        ]);

        return $this->sheet('التصنيفات', ['الاسم', 'عدد المنتجات'], $rows);
    }

    private function inventory(): array
    {
        $query = Product::with('category')->where('type', 'product');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'current_stock' => ['column' => 'current_stock', 'type' => 'number'],
            'min_stock' => ['column' => 'min_stock', 'type' => 'number'],
        ]);
        if (request()->filled('stock_value')) {
            $query->whereRaw('(current_stock * cost_price) = ?', [request('stock_value')]);
        }
        $query->when(request('stock_status') === 'out', fn ($q) => $q->where('current_stock', '<=', 0))
            ->when(request('stock_status') === 'low', fn ($q) => $q->where('current_stock', '>', 0)->whereColumn('current_stock', '<=', 'min_stock'))
            ->when(request('stock_status') === 'ok', fn ($q) => $q->whereColumn('current_stock', '>', 'min_stock'));

        $rows = $query->orderBy('name')->get()->map(fn (Product $product) => [
            $product->name,
            $this->qty($product->current_stock),
            $this->qty($product->min_stock),
            $this->money($product->current_stock * $product->cost_price),
            $this->stockLabel($product),
        ]);

        return $this->sheet('أرصدة المخزون', ['المنتج', 'المخزون', 'الحد الأدنى', 'القيمة', 'الحالة'], $rows);
    }

    private function inventoryMovements(): array
    {
        $labels = ['in' => 'إضافة', 'out' => 'صرف', 'adjust' => 'تسوية'];
        $query = InventoryMovement::with('product', 'creator')->latest();
        if (request()->filled('product')) {
            $query->where('product_id', request('product'));
        }

        $rows = $query->get()->map(fn (InventoryMovement $movement) => [
            $movement->product->name ?? '-',
            $labels[$movement->type] ?? $movement->type,
            $this->qty($movement->qty),
            $this->day($movement->moved_at ?: $movement->created_at),
            $movement->notes ?: '—',
            $movement->creator->name ?? '-',
        ]);

        return $this->sheet('حركات المخزون', ['المنتج', 'النوع', 'الكمية', 'التاريخ', 'ملاحظات', 'المستخدم'], $rows);
    }

    private function sales(): array
    {
        $query = SalesInvoice::with('customer', 'creator');
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

        $rows = $query->latest()->get()->map(fn (SalesInvoice $invoice) => [
            $invoice->number,
            $invoice->customer->name ?? '-',
            $this->day($invoice->invoice_date),
            invoice_status_label($invoice->status),
            $this->money($invoice->total),
            $this->money($invoice->remaining),
            $invoice->creator->name ?? 'غير محدد',
            $this->stamp($invoice->created_at),
        ]);

        return $this->sheet('فواتير المبيعات', ['الرقم', 'اسم العميل', 'تاريخ البيع', 'الحالة', 'الإجمالي', 'المتبقي', 'أنشئت بواسطة', 'تاريخ الإنشاء'], $rows);
    }

    private function salesReturns(): array
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

        $rows = $query->latest()->get()->map(fn (SalesReturn $return) => [
            $return->number,
            $return->customer->name ?? '-',
            $return->invoice->number ?? '-',
            $this->day($return->return_date),
            $this->money($return->total),
            invoice_status_label($return->status),
            $return->creator->name ?? 'غير محدد',
            $this->stamp($return->created_at),
        ]);

        return $this->sheet('مرتجعات المبيعات', ['الرقم', 'العميل', 'الفاتورة', 'التاريخ', 'الإجمالي', 'الحالة', 'أنشئت بواسطة', 'تاريخ الإنشاء'], $rows);
    }

    private function purchases(): array
    {
        $query = PurchaseInvoice::with('supplier', 'creator');
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

        $rows = $query->latest()->get()->map(fn (PurchaseInvoice $invoice) => [
            $invoice->number,
            $invoice->supplier->name ?? '-',
            $this->day($invoice->invoice_date),
            invoice_status_label($invoice->status),
            $this->money($invoice->total),
            $this->money($invoice->remaining),
            $invoice->creator->name ?? 'غير محدد',
            $this->stamp($invoice->created_at),
        ]);

        return $this->sheet('فواتير المشتريات', ['الرقم', 'اسم المورد', 'تاريخ الشراء', 'الحالة', 'الإجمالي', 'المتبقي', 'أنشئت بواسطة', 'تاريخ الإنشاء'], $rows);
    }

    private function purchaseReturns(): array
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

        $rows = $query->latest()->get()->map(fn (PurchaseReturn $return) => [
            $return->number,
            $return->supplier->name ?? '-',
            $return->invoice->number ?? '-',
            $this->day($return->return_date),
            $this->money($return->total),
            invoice_status_label($return->status),
            $return->creator->name ?? 'غير محدد',
            $this->stamp($return->created_at),
        ]);

        return $this->sheet('مرتجعات المشتريات', ['الرقم', 'المورد', 'الفاتورة', 'التاريخ', 'الإجمالي', 'الحالة', 'أنشئت بواسطة', 'تاريخ الإنشاء'], $rows);
    }

    private function customers(): array
    {
        $query = Customer::with('creator');
        GridQuery::apply($query, [
            'code' => ['column' => 'code', 'type' => 'text'],
            'name' => ['column' => 'name', 'type' => 'select'],
            'phone' => ['column' => 'phone', 'type' => 'text'],
            'address' => ['column' => 'address', 'type' => 'text'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        $rows = $query->latest()->get()->map(fn (Customer $customer) => [
            $customer->code,
            $customer->name,
            $customer->phone ?: '-',
            $customer->address ?: '-',
            $this->money($customer->current_balance),
            $customer->creator->name ?? 'غير محدد',
            $this->stamp($customer->created_at),
        ]);

        return $this->sheet('العملاء', ['الكود', 'اسم العميل', 'الموبايل', 'العنوان', 'الرصيد', 'أضيف بواسطة', 'تاريخ الإضافة'], $rows);
    }

    private function customerAccounts(): array
    {
        $posted = InvoiceStatus::posted();
        $query = Customer::query()
            ->withSum(['invoices as sales_total' => fn ($q) => $q->whereIn('status', $posted)], 'total')
            ->withSum(['invoices as paid_total' => fn ($q) => $q->whereIn('status', $posted)], 'paid');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
        ]);
        if (request()->filled('sales')) {
            $query->having('sales_total', '=', request('sales'));
        }
        if (request()->filled('paid')) {
            $query->having('paid_total', '=', request('paid'));
        }

        $rows = $query->orderByDesc('current_balance')->get()->map(fn (Customer $customer) => [
            $customer->name,
            $this->money($customer->sales_total),
            $this->money($customer->paid_total),
            $this->money($customer->current_balance),
        ]);

        return $this->sheet('حسابات العملاء', ['العميل', 'المبيعات', 'المدفوع', 'الرصيد'], $rows);
    }

    private function suppliers(): array
    {
        $query = Supplier::with('creator');
        GridQuery::apply($query, [
            'code' => ['column' => 'code', 'type' => 'text'],
            'name' => ['column' => 'name', 'type' => 'select'],
            'phone' => ['column' => 'phone', 'type' => 'text'],
            'address' => ['column' => 'address', 'type' => 'text'],
            'tax_number' => ['column' => 'tax_number', 'type' => 'text'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        $rows = $query->latest()->get()->map(fn (Supplier $supplier) => [
            $supplier->code,
            $supplier->name,
            $supplier->phone ?: '-',
            $supplier->address ?: '-',
            $supplier->tax_number ?: '-',
            $this->money($supplier->current_balance),
            $supplier->creator->name ?? 'غير محدد',
            $this->stamp($supplier->created_at),
        ]);

        return $this->sheet('الموردون', ['الكود', 'اسم المورد', 'الموبايل', 'العنوان', 'السجل التجاري', 'الرصيد', 'أضيف بواسطة', 'تاريخ الإضافة'], $rows);
    }

    private function supplierAccounts(): array
    {
        $posted = InvoiceStatus::posted();
        $query = Supplier::query()
            ->withSum(['invoices as purchases_total' => fn ($q) => $q->whereIn('status', $posted)], 'total')
            ->withSum(['invoices as paid_total' => fn ($q) => $q->whereIn('status', $posted)], 'paid');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'balance' => ['column' => 'current_balance', 'type' => 'number'],
        ]);
        if (request()->filled('purchases')) {
            $query->having('purchases_total', '=', request('purchases'));
        }
        if (request()->filled('paid')) {
            $query->having('paid_total', '=', request('paid'));
        }

        $rows = $query->orderByDesc('current_balance')->get()->map(fn (Supplier $supplier) => [
            $supplier->name,
            $this->money($supplier->purchases_total),
            $this->money($supplier->paid_total),
            $this->money($supplier->current_balance),
        ]);

        return $this->sheet('حسابات الموردين', ['المورد', 'المشتريات', 'المدفوع', 'الرصيد'], $rows);
    }

    private function partyInvoices(string $model, string $key, string $title): array
    {
        $party = $model::query()->with(['invoices' => fn ($q) => $q->latest('id')])->findOrFail(request($key));
        $rows = $party->invoices->map(fn ($invoice) => [
            $invoice->number,
            $this->day($invoice->invoice_date),
            $this->money($invoice->total),
            $this->money($invoice->remaining),
            invoice_status_label($invoice->status),
        ]);

        return $this->sheet($title.' '.$party->name, ['الرقم', 'التاريخ', 'الإجمالي', 'المتبقي', 'الحالة'], $rows);
    }

    private function partyPayments(string $model, string $key, string $title): array
    {
        $labels = ['receipt' => 'تحصيل', 'payment' => 'صرف'];
        $party = $model::query()->with(['payments' => fn ($q) => $q->latest('id')])->findOrFail(request($key));
        $rows = $party->payments->map(fn ($payment) => [
            $this->money($payment->amount),
            $this->day($payment->transacted_at),
            $labels[$payment->type] ?? $payment->type,
        ]);

        return $this->sheet($title.' '.$party->name, ['المبلغ', 'التاريخ', 'النوع'], $rows);
    }

    private function expenses(): array
    {
        $query = MoneyEntry::with('category', 'treasury');
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'type' => ['column' => 'type', 'type' => 'select'],
            'money_category_id' => ['column' => 'money_category_id', 'type' => 'select'],
            'treasury_id' => ['column' => 'treasury_id', 'type' => 'select'],
            'amount' => ['column' => 'amount', 'type' => 'number'],
            'entry_date' => ['column' => 'entry_date', 'type' => 'date'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        $rows = $query->latest()->get()->map(fn (MoneyEntry $entry) => [
            $entry->number,
            $entry->type === 'expense' ? 'مصروف' : 'إيراد',
            $entry->category->name ?? '-',
            $entry->treasury->name ?? '-',
            $this->money($entry->amount),
            $this->day($entry->entry_date),
            $this->stamp($entry->created_at),
        ]);

        return $this->sheet('المصروفات والإيرادات', ['الرقم', 'النوع', 'التصنيف', 'الخزينة', 'المبلغ', 'التاريخ', 'تاريخ التسجيل'], $rows);
    }

    private function treasuryMoves(string $type, string $title, string $partyHeading): array
    {
        $rows = MoneyTransaction::with('treasury', 'party')->where('type', $type)->latest()->get()
            ->map(fn (MoneyTransaction $row) => [
                $row->party->name ?? '-',
                $row->treasury->name ?? '-',
                $this->money($row->amount),
                $this->day($row->transacted_at),
            ]);

        return $this->sheet($title, [$partyHeading, 'الخزينة', 'المبلغ', 'التاريخ'], $rows);
    }

    private function treasuryTransfers(): array
    {
        $rows = TreasuryTransfer::with('fromTreasury', 'toTreasury')->latest()->get()
            ->map(fn (TreasuryTransfer $row) => [
                $row->fromTreasury->name ?? '-',
                $row->toTreasury->name ?? '-',
                $this->money($row->amount),
                $this->day($row->transferred_at),
            ]);

        return $this->sheet('تحويلات الخزينة', ['من', 'إلى', 'المبلغ', 'التاريخ'], $rows);
    }

    private function accounts(): array
    {
        $typeLabels = [
            'asset' => 'أصل',
            'liability' => 'التزام',
            'equity' => 'ملكية',
            'revenue' => 'إيراد',
            'expense' => 'مصروف',
        ];
        $accounts = GridQuery::applyCollection(Account::query()->with('parent')->orderBy('code')->get(), [
            'code' => ['key' => 'code', 'type' => 'text'],
            'name' => ['key' => 'name', 'type' => 'select'],
            'type' => ['key' => 'type', 'type' => 'select'],
            'parent_id' => ['key' => 'parent_id', 'type' => 'select'],
            'balance' => ['key' => 'current_balance', 'type' => 'number'],
            'is_active' => ['key' => 'is_active', 'type' => 'bool'],
        ]);

        $rows = $accounts->map(fn (Account $account) => [
            $account->code,
            $account->name,
            $typeLabels[$account->type] ?? $account->type,
            $account->parent->name ?? '-',
            $this->money($account->current_balance),
            $account->is_active ? 'نشط' : 'غير نشط',
        ]);

        return $this->sheet('دليل الحسابات', ['الكود', 'الاسم', 'النوع', 'الحساب الأب', 'الرصيد', 'الحالة'], $rows);
    }

    private function balances(string $model, string $title, string $nameHeading): array
    {
        $rows = GridQuery::applyCollection($model::query()->orderByDesc('current_balance')->get(), [
            'code' => ['key' => 'code', 'type' => 'text'],
            'name' => ['key' => 'name', 'type' => 'select'],
            'phone' => ['key' => 'phone', 'type' => 'text'],
            'balance' => ['key' => 'current_balance', 'type' => 'number'],
        ])->map(fn ($party) => [
            $party->code,
            $party->name,
            $party->phone ?: '-',
            $this->money($party->current_balance),
        ]);

        return $this->sheet($title, ['الكود', $nameHeading, 'الموبايل', 'الرصيد'], $rows);
    }

    private function journals(): array
    {
        $query = Journal::with('creator');
        GridQuery::apply($query, [
            'number' => ['column' => 'number', 'type' => 'text'],
            'journal_date' => ['column' => 'journal_date', 'type' => 'date'],
            'description' => ['column' => 'description', 'type' => 'text'],
            'created_by' => ['column' => 'created_by', 'type' => 'select'],
        ]);

        $rows = $query->latest()->get()->map(fn (Journal $journal) => [
            $journal->number,
            $this->day($journal->journal_date),
            $journal->description ?: '-',
            $journal->creator->name ?? '-',
        ]);

        return $this->sheet('القيود', ['الرقم', 'التاريخ', 'الوصف', 'المستخدم'], $rows);
    }

    private function journalLines(): array
    {
        $journal = Journal::query()->with('lines.account')->findOrFail(request('journal'));
        $rows = $journal->lines->map(fn ($line) => [
            $line->account->code ?? '-',
            $line->account->name ?? '-',
            $this->money($line->debit),
            $this->money($line->credit),
        ]);

        return $this->sheet('قيد '.$journal->number, ['الكود', 'الحساب', 'مدين', 'دائن'], $rows);
    }

    private function users(): array
    {
        $labels = ['active' => 'نشط', 'disabled' => 'معطّل', 'suspended' => 'موقوف'];
        $query = User::with('role');
        GridQuery::apply($query, [
            'name' => ['column' => 'name', 'type' => 'select'],
            'email' => ['column' => 'email', 'type' => 'text'],
            'role_id' => ['column' => 'role_id', 'type' => 'select'],
            'status' => ['column' => 'status', 'type' => 'select'],
        ]);

        $rows = $query->latest()->get()->map(fn (User $user) => [
            $user->name,
            $user->email,
            $user->role->name ?? '-',
            $labels[$user->status] ?? $user->status,
        ]);

        return $this->sheet('المستخدمون', ['الاسم', 'البريد', 'الدور', 'الحالة'], $rows);
    }

    private function roles(): array
    {
        $roles = Role::withCount('users')->with('permissions')->latest()->get();
        $roles = GridQuery::applyCollection($roles, [
            'name' => ['key' => 'name', 'type' => 'select'],
            'description' => ['key' => 'description', 'type' => 'text'],
            'users_count' => ['key' => 'users_count', 'type' => 'number'],
        ]);
        if (request()->filled('permission')) {
            $needle = (string) request('permission');
            $roles = $roles->filter(fn (Role $role) => $role->permissions->contains(
                fn ($permission) => mb_stripos($permission->name, $needle) !== false
            ))->values();
        }

        $rows = $roles->map(fn (Role $role) => [
            $role->name,
            $role->description ?: '-',
            $role->permissions->count(),
            (int) $role->users_count,
        ]);

        return $this->sheet('الأدوار', ['اسم الدور', 'الوصف', 'الصلاحيات', 'المستخدمون'], $rows);
    }

    private function logs(): array
    {
        $modules = [
            'customers' => 'العملاء', 'suppliers' => 'الموردون', 'products' => 'المنتجات',
            'sales' => 'المبيعات', 'purchases' => 'المشتريات', 'treasury' => 'الخزينة',
            'expenses' => 'المصروفات', 'inventory' => 'المخزون', 'users' => 'المستخدمون',
            'accounting' => 'الحسابات',
        ];
        $actions = [
            'create' => 'إضافة', 'update' => 'تعديل', 'delete' => 'حذف', 'confirm' => 'تأكيد',
            'receipt' => 'تحصيل', 'payment' => 'صرف', 'transfer' => 'تحويل', 'return' => 'مرتجع',
            'in' => 'إضافة مخزون', 'out' => 'صرف مخزون', 'adjust' => 'تسوية',
            'expense' => 'مصروف', 'revenue' => 'إيراد',
        ];
        $query = ActivityLog::with('user');
        GridQuery::apply($query, [
            'description' => ['column' => 'description', 'type' => 'text'],
            'module' => ['column' => 'module', 'type' => 'select'],
            'action' => ['column' => 'action', 'type' => 'select'],
            'user_id' => ['column' => 'user_id', 'type' => 'select'],
            'created_on' => ['column' => 'created_at', 'type' => 'date'],
        ]);

        $rows = $query->latest()->get()->map(fn (ActivityLog $log) => [
            $log->description,
            $modules[$log->module] ?? $log->module,
            $actions[$log->action] ?? $log->action,
            $log->user->name ?? '-',
            $this->stamp($log->created_at),
        ]);

        return $this->sheet('سجل العمليات', ['الوصف', 'الوحدة', 'الإجراء', 'المستخدم', 'التاريخ'], $rows);
    }

    private function treasuryActivity(): array
    {
        $rows = MoneyTransaction::with('treasury', 'party')->latest()->get()
            ->map(fn (MoneyTransaction $row) => [
                $row->type === 'receipt' ? 'تحصيل' : 'صرف',
                $row->party->name ?? '-',
                $this->money($row->amount),
                $row->treasury->name ?? '-',
                $this->day($row->transacted_at),
            ]);

        return $this->sheet('التحصيلات والمدفوعات', ['النوع', 'الطرف', 'المبلغ', 'الخزينة', 'التاريخ'], $rows);
    }

    private function invoiceItems(string $model, string $key, string $title, string $priceField): array
    {
        $invoice = $model::query()->with('items.product')->findOrFail(request($key));
        $rows = $invoice->items->map(fn ($item) => [
            $item->product->name ?? '—',
            $item->description ?: ($item->product->description ?? '—'),
            $this->qty($item->qty),
            $this->money($item->{$priceField} ?? $item->unit_price ?? 0),
            $this->money($item->total),
        ]);

        return $this->sheet($title.' '.$invoice->number, ['النوع', 'الوصف', 'الكمية', 'سعر الوحدة', 'المبلغ'], $rows);
    }

    private function returnItems(string $model, string $key, string $title, string $priceField): array
    {
        $return = $model::query()->with('items.product')->findOrFail(request($key));
        $rows = $return->items->map(fn ($item) => [
            $item->product->name ?? '-',
            $this->qty($item->qty),
            $this->money($item->{$priceField} ?? 0),
            $this->money($item->total),
        ]);

        return $this->sheet($title.' '.$return->number, ['المنتج', 'الكمية', 'السعر', 'الإجمالي'], $rows);
    }

    private function reportSheet(callable $loader): array
    {
        $data = $loader();
        $rows = collect($data['rows']);
        foreach ($data['headers'] as $index => $header) {
            if (request()->filled('col_'.$index)) {
                $expected = (string) request('col_'.$index);
                $rows = $rows->filter(fn ($row) => (string) ($row[$index] ?? '') === $expected)->values();
            }
        }

        return $this->sheet($data['title'], $data['headers'], $rows->map(fn ($row) => array_values((array) $row)));
    }

    private function profit(): array
    {
        [$from, $to] = $this->reports->period(request('from'), request('to'));
        $data = $this->reports->profit($from, $to);
        $lines = [
            'المبيعات' => $this->money($data['sales']),
            'إيرادات أخرى' => $this->money($data['revenues']),
            'المشتريات' => $this->money($data['purchases']),
            'المصروفات' => $this->money($data['expenses']),
        ];
        if (request()->filled('item') && array_key_exists((string) request('item'), $lines)) {
            $lines = [request('item') => $lines[request('item')]];
        }
        $rows = collect($lines)->map(fn ($amount, $label) => [$label, $amount]);
        $net = $data['sales'] + $data['revenues'] - $data['purchases'] - $data['expenses'];
        if (! request()->filled('item')) {
            $rows->push([$net >= 0 ? 'صافي الربح' : 'صافي الخسارة', $this->money(abs($net))]);
        }

        return $this->sheet('الأرباح والخسائر', ['البند', 'المبلغ'], $rows);
    }

    private function financial(): array
    {
        $labels = [
            'asset' => 'الأصول',
            'liability' => 'الالتزامات',
            'equity' => 'حقوق الملكية',
            'revenue' => 'الإيرادات',
            'expense' => 'المصروفات',
        ];
        $grouped = $this->reports->financial()['accounts'];
        $rows = collect();
        foreach ($grouped as $type => $accounts) {
            foreach ($accounts as $account) {
                $rows->push([
                    $labels[$type] ?? $type,
                    $account->code,
                    $account->name,
                    $this->money($account->current_balance),
                ]);
            }
        }

        return $this->sheet('التقارير المالية', ['التصنيف', 'الكود', 'الحساب', 'الرصيد'], $rows);
    }

    private function ledger(): array
    {
        $account = request()->filled('account_id') ? Account::query()->find(request('account_id')) : null;
        if (! $account) {
            return $this->sheet('دفتر الأستاذ', ['رقم القيد', 'البيان', 'التاريخ', 'مدين', 'دائن'], collect());
        }

        $query = JournalLine::with('journal')->where('account_id', $account->id);
        if (request()->filled('journal_number')) {
            $query->whereHas('journal', fn ($q) => $q->where('number', 'like', '%'.request('journal_number').'%'));
        }
        if (request()->filled('description')) {
            $query->whereHas('journal', fn ($q) => $q->where('description', 'like', '%'.request('description').'%'));
        }
        if (request()->filled('journal_date')) {
            $query->whereHas('journal', fn ($q) => $q->whereDate('journal_date', request('journal_date')));
        }
        if (request()->filled('debit')) {
            $query->where('debit', request('debit'));
        }
        if (request()->filled('credit')) {
            $query->where('credit', request('credit'));
        }

        $rows = $query->latest()->get()->map(fn (JournalLine $line) => [
            $line->journal->number ?? '-',
            $line->journal->description ?? '-',
            $this->day($line->journal?->journal_date ?? $line->created_at),
            $this->money($line->debit),
            $this->money($line->credit),
        ]);

        return $this->sheet('دفتر الأستاذ '.$account->code, ['رقم القيد', 'البيان', 'التاريخ', 'مدين', 'دائن'], $rows);
    }
}
