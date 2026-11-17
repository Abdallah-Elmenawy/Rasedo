<?php

namespace App\Repositories\Reports\Eloquent;

use App\Enums\InvoiceStatus;
use App\Enums\MoneyEntryType;
use App\Models\PublicSettings\ActivityLog;
use App\Models\Customers\Customer;
use App\Models\Expenses\MoneyEntry;
use App\Models\Treasury\MoneyTransaction;
use App\Models\Products\Product;
use App\Models\Purchases\PurchaseInvoice;
use App\Models\Sales\SalesInvoice;
use App\Models\Sales\SalesInvoiceItem;
use App\Models\Suppliers\Supplier;
use App\Models\Treasury\Treasury;
use App\Repositories\Reports\Contracts\DashboardRepositoryInterface;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function snapshot(): array
    {
        $posted = InvoiceStatus::posted();
        $sales = (float) SalesInvoice::query()->whereIn('status', $posted)->sum('total');
        $purchases = (float) PurchaseInvoice::query()->whereIn('status', $posted)->sum('total');
        $expenses = (float) MoneyEntry::query()->where('type', MoneyEntryType::Expense->value)->sum('amount');
        $revenues = (float) MoneyEntry::query()->where('type', MoneyEntryType::Revenue->value)->sum('amount');
        $profit = $sales + $revenues - $purchases - $expenses;
        $receivable = (float) Customer::query()->sum('current_balance');
        $payable = (float) Supplier::query()->sum('current_balance');
        $treasury = (float) Treasury::query()->sum('current_balance');
        $inventoryValue = (float) Product::query()->where('type', 'product')->get()->sum(fn ($product) => $product->current_stock * $product->cost_price);
        $kpiItems = [
            ['title' => 'مبيعات مؤكدة', 'value' => $sales, 'count' => SalesInvoice::query()->whereIn('status', $posted)->count(), 'color' => 'blue', 'icon' => 'fe-shopping-cart'],
            ['title' => 'مشتريات مؤكدة', 'value' => $purchases, 'count' => PurchaseInvoice::query()->whereIn('status', $posted)->count(), 'color' => 'pink', 'icon' => 'fe-file-text'],
            ['title' => 'المصروفات', 'value' => $expenses, 'count' => MoneyEntry::query()->where('type', MoneyEntryType::Expense->value)->count(), 'color' => 'red', 'icon' => 'fe-minus-circle'],
            ['title' => 'صافي الربح', 'value' => $profit, 'count' => 0, 'color' => 'green', 'icon' => 'fe-trending-up'],
            ['title' => 'الذمم المدينة', 'value' => $receivable, 'count' => Customer::query()->where('current_balance', '>', 0)->count(), 'color' => 'cyan', 'icon' => 'fe-users'],
            ['title' => 'الذمم الدائنة', 'value' => $payable, 'count' => Supplier::query()->where('current_balance', '>', 0)->count(), 'color' => 'purple', 'icon' => 'fe-truck'],
            ['title' => 'رصيد الخزينة', 'value' => $treasury, 'count' => Treasury::query()->where('is_active', true)->count(), 'color' => 'amber', 'icon' => 'fe-credit-card'],
            ['title' => 'قيمة المخزون', 'value' => $inventoryValue, 'count' => Product::query()->where('type', 'product')->count(), 'color' => 'orange', 'icon' => 'fe-box'],
        ];
        $kpiTotal = collect($kpiItems)->sum(fn ($item) => abs((float) $item['value']));

        return [
            'kpiCards' => collect($kpiItems)->map(function ($item) use ($kpiTotal) {
                $item['percent'] = $kpiTotal > 0 ? (int) round((abs((float) $item['value']) / $kpiTotal) * 100) : 0;
                $item['up'] = (float) $item['value'] >= 0;

                return $item;
            }),
            'sales' => $sales,
            'purchases' => $purchases,
            'expenses' => $expenses,
            'profit' => $sales + $revenues - $purchases - $expenses,
            'collected' => (float) SalesInvoice::query()->whereIn('status', $posted)->sum('paid'),
            'receivable' => $receivable,
            'payable' => $payable,
            'treasury' => $treasury,
            'inventoryValue' => $inventoryValue,
            'latestSales' => SalesInvoice::with('customer')->latest()->limit(6)->get(),
            'latestPayments' => MoneyTransaction::with('party', 'treasury')->latest()->limit(6)->get(),
            'lowStock' => Product::query()->where('type', 'product')->whereColumn('current_stock', '<=', 'min_stock')->limit(6)->get(),
            'activities' => ActivityLog::with('user')->latest()->limit(8)->get(),
            'charts' => $this->movement($posted),
            'currency' => company()?->currency_symbol ?? 'ج.م',
            'topProducts' => SalesInvoiceItem::query()
                ->select('product_id', DB::raw('SUM(qty) as qty_sold'), DB::raw('SUM(total) as amount'))
                ->whereHas('invoice', fn ($q) => $q->whereIn('status', $posted))
                ->groupBy('product_id')->orderByDesc('qty_sold')->with('product')->limit(5)->get(),
        ];
    }

    private function movement(array $posted): array
    {
        $today = now();

        return [
            'day' => $this->byHour($posted, $today),
            'week' => $this->byDay($posted, $today->copy()->subDays(6)->startOfDay(), $today->copy()->endOfDay()),
            'month' => $this->byDay($posted, $today->copy()->startOfMonth(), $today->copy()->endOfDay()),
            'year' => $this->byMonth($posted, (int) $today->year),
        ];
    }

    private function byHour(array $posted, $today): array
    {
        $from = $today->copy()->startOfDay();
        $to = $today->copy()->endOfDay();
        $sales = $this->buckets(SalesInvoice::query()->whereIn('status', $posted)->whereBetween('created_at', [$from, $to]), 'HOUR(created_at)', 'total');
        $purchases = $this->buckets(PurchaseInvoice::query()->whereIn('status', $posted)->whereBetween('created_at', [$from, $to]), 'HOUR(created_at)', 'total');
        $expenses = $this->buckets(MoneyEntry::query()->where('type', MoneyEntryType::Expense->value)->whereBetween('created_at', [$from, $to]), 'HOUR(created_at)', 'amount');
        $revenues = $this->buckets(MoneyEntry::query()->where('type', MoneyEntryType::Revenue->value)->whereBetween('created_at', [$from, $to]), 'HOUR(created_at)', 'amount');

        $labels = [];
        $series = ['sales' => [], 'purchases' => [], 'expenses' => [], 'revenues' => []];
        for ($hour = 0; $hour < 24; $hour += 2) {
            $labels[] = sprintf('%02d:00', $hour);
            foreach (['sales' => $sales, 'purchases' => $purchases, 'expenses' => $expenses, 'revenues' => $revenues] as $key => $rows) {
                $series[$key][] = (float) ($rows[$hour] ?? 0) + (float) ($rows[$hour + 1] ?? 0);
            }
        }

        return ['labels' => $labels] + $series;
    }

    private function byDay(array $posted, $from, $to): array
    {
        $start = $from->toDateString();
        $end = $to->toDateString();
        $sales = $this->buckets(SalesInvoice::query()->whereIn('status', $posted)->whereBetween('invoice_date', [$start, $end]), 'invoice_date', 'total');
        $purchases = $this->buckets(PurchaseInvoice::query()->whereIn('status', $posted)->whereBetween('invoice_date', [$start, $end]), 'invoice_date', 'total');
        $expenses = $this->buckets(MoneyEntry::query()->where('type', MoneyEntryType::Expense->value)->whereBetween('entry_date', [$start, $end]), 'entry_date', 'amount');
        $revenues = $this->buckets(MoneyEntry::query()->where('type', MoneyEntryType::Revenue->value)->whereBetween('entry_date', [$start, $end]), 'entry_date', 'amount');

        $labels = [];
        $series = ['sales' => [], 'purchases' => [], 'expenses' => [], 'revenues' => []];
        $cursor = $from->copy()->startOfDay();
        $last = $to->copy()->startOfDay();
        while ($cursor->lte($last)) {
            $key = $cursor->toDateString();
            $labels[] = $cursor->format('m-d');
            foreach (['sales' => $sales, 'purchases' => $purchases, 'expenses' => $expenses, 'revenues' => $revenues] as $name => $rows) {
                $series[$name][] = (float) ($rows[$key] ?? 0);
            }
            $cursor->addDay();
        }

        return ['labels' => $labels] + $series;
    }

    private function byMonth(array $posted, int $year): array
    {
        $names = [1 => 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
        $sales = $this->buckets(SalesInvoice::query()->whereIn('status', $posted)->whereYear('invoice_date', $year), 'MONTH(invoice_date)', 'total');
        $purchases = $this->buckets(PurchaseInvoice::query()->whereIn('status', $posted)->whereYear('invoice_date', $year), 'MONTH(invoice_date)', 'total');
        $expenses = $this->buckets(MoneyEntry::query()->where('type', MoneyEntryType::Expense->value)->whereYear('entry_date', $year), 'MONTH(entry_date)', 'amount');
        $revenues = $this->buckets(MoneyEntry::query()->where('type', MoneyEntryType::Revenue->value)->whereYear('entry_date', $year), 'MONTH(entry_date)', 'amount');

        $labels = [];
        $series = ['sales' => [], 'purchases' => [], 'expenses' => [], 'revenues' => []];
        for ($month = 1; $month <= 12; $month++) {
            $labels[] = $names[$month];
            foreach (['sales' => $sales, 'purchases' => $purchases, 'expenses' => $expenses, 'revenues' => $revenues] as $name => $rows) {
                $series[$name][] = (float) ($rows[$month] ?? $rows[(string) $month] ?? 0);
            }
        }

        return ['labels' => $labels] + $series;
    }

    private function buckets($query, string $expr, string $sum): array
    {
        return $query->selectRaw($expr.' as bucket, SUM('.$sum.') as total')
            ->groupBy(DB::raw($expr))
            ->pluck('total', 'bucket')
            ->all();
    }
}
