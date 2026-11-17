<?php

namespace App\Services\Reports;

use App\Repositories\Expenses\Contracts\MoneyEntryRepositoryInterface;
use App\Support\GridQuery;
use App\Repositories\Purchases\Contracts\PurchaseInvoiceRepositoryInterface;
use App\Repositories\Reports\Contracts\ReportRepositoryInterface;
use App\Repositories\Sales\Contracts\SalesInvoiceRepositoryInterface;

class ReportService
{
    public function __construct(
        private readonly ReportRepositoryInterface $reports,
        private readonly SalesInvoiceRepositoryInterface $sales,
        private readonly PurchaseInvoiceRepositoryInterface $purchases,
        private readonly MoneyEntryRepositoryInterface $entries,
    ) {}

    public function period(?string $from, ?string $to): array
    {
        return [
            $from ?: now()->startOfMonth()->toDateString(),
            $to ?: now()->toDateString(),
        ];
    }

    public function sales(string $from, string $to): array
    {
        $rows = $this->sales->postedBetween($from, $to);

        return [
            'title' => 'تقرير المبيعات', 'from' => $from, 'to' => $to,
            'headers' => ['الرقم', 'العميل', 'التاريخ', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة'],
            'rows' => $rows->map(fn ($row) => [$row->number, $row->customer->name, $row->invoice_date->format('Y-m-d'), money($row->total), money($row->paid), money($row->remaining), invoice_status_label($row->status)]),
            'total' => money($rows->sum('total')),
        ];
    }

    public function purchases(string $from, string $to): array
    {
        $rows = $this->purchases->postedBetween($from, $to);

        return [
            'title' => 'تقرير المشتريات', 'from' => $from, 'to' => $to,
            'headers' => ['الرقم', 'المورد', 'التاريخ', 'الإجمالي', 'المدفوع', 'المتبقي', 'الحالة'],
            'rows' => $rows->map(fn ($row) => [$row->number, $row->supplier->name, $row->invoice_date->format('Y-m-d'), money($row->total), money($row->paid), money($row->remaining), invoice_status_label($row->status)]),
            'total' => money($rows->sum('total')),
        ];
    }

    public function inventory(): array
    {
        $products = $this->reports->inventoryProducts();

        return [
            'title' => 'تقرير المخزون', 'from' => null, 'to' => null,
            'headers' => ['SKU', 'المنتج', 'التصنيف', 'المخزون', 'الحد الأدنى', 'القيمة', 'تنبيه'],
            'rows' => $products->map(fn ($product) => [$product->sku, $product->name, $product->category->name ?? '-', qty($product->current_stock), qty($product->min_stock), money($product->current_stock * $product->cost_price), $product->isLowStock() ? 'منخفض' : '-']),
            'total' => money($products->sum(fn ($product) => $product->current_stock * $product->cost_price)),
        ];
    }

    public function customers(): array
    {
        $customers = $this->reports->customersByBalance();

        return [
            'title' => 'تقرير العملاء', 'from' => null, 'to' => null,
            'headers' => ['الكود', 'العميل', 'الهاتف', 'الرصيد'],
            'rows' => $customers->map(fn ($customer) => [$customer->code, $customer->name, $customer->phone, money($customer->current_balance)]),
            'total' => money($customers->sum('current_balance')),
        ];
    }

    public function suppliers(): array
    {
        $suppliers = $this->reports->suppliersByBalance();

        return [
            'title' => 'تقرير الموردين', 'from' => null, 'to' => null,
            'headers' => ['الكود', 'المورد', 'الهاتف', 'الرصيد'],
            'rows' => $suppliers->map(fn ($supplier) => [$supplier->code, $supplier->name, $supplier->phone, money($supplier->current_balance)]),
            'total' => money($suppliers->sum('current_balance')),
        ];
    }

    public function expenses(string $from, string $to): array
    {
        $rows = $this->entries->betweenDates($from, $to);

        return [
            'title' => 'تقرير المصروفات والإيرادات', 'from' => $from, 'to' => $to,
            'headers' => ['الرقم', 'النوع', 'التصنيف', 'الخزينة', 'المبلغ', 'التاريخ'],
            'rows' => $rows->map(fn ($row) => [$row->number, $row->type === 'expense' ? 'مصروف' : 'إيراد', $row->category->name ?? '-', $row->treasury->name ?? '-', money($row->amount), $row->entry_date->format('Y-m-d')]),
            'total' => 'مصروف '.money($rows->where('type', 'expense')->sum('amount')).' / إيراد '.money($rows->where('type', 'revenue')->sum('amount')),
        ];
    }

    public function profit(string $from, string $to): array
    {
        return ['from' => $from, 'to' => $to] + $this->reports->profit($from, $to);
    }

    public function financial(): array
    {
        $flat = $this->reports->accountsByType()->flatten(1);
        $filters = [
            'names' => $flat->pluck('name', 'name')->filter()->all(),
        ];
        $accounts = GridQuery::applyCollection($flat, [
            'code' => ['key' => 'code', 'type' => 'text'],
            'name' => ['key' => 'name', 'type' => 'select'],
            'balance' => ['key' => 'current_balance', 'type' => 'number'],
        ])->groupBy('type');

        return compact('accounts', 'filters');
    }

    public function ledger(mixed $accountId): array
    {
        return $this->reports->ledger($accountId);
    }
}
