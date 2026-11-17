@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'فواتير المشتريات',
        'action' => can('purchases.create') ? '<a href="'.route('purchases.create').'" class="btn btn-primary"><i class="fe fe-plus ml-1"></i> إضافة فاتورة</a>' : '',
        'export' => 'purchases',
    ])
@endsection
@section('content')
<x-filter-drawer>
    <x-filter-field label="الرقم" name="number" placeholder="أدخل رقم الفاتورة..." />
    <x-filter-field label="اسم المورد" name="supplier_id" type="select" :options="$filters['suppliers']" />
    <x-filter-field label="تاريخ الشراء" name="invoice_date" type="date" />
    <x-filter-field label="الحالة" name="status" type="select" :options="$filters['statuses']" />
    <x-filter-field label="الإجمالي" name="total" type="number" placeholder="أدخل الإجمالي..." />
    <x-filter-field label="المتبقي" name="remaining" type="number" placeholder="أدخل المتبقي..." />
    <x-filter-field label="أنشئت بواسطة" name="created_by" type="select" :options="$filters['creators']" />
    <x-filter-field label="تاريخ الإنشاء" name="created_on" type="date" />
</x-filter-drawer>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-truck"></i> فواتير المشتريات</h4>
        <span class="count-badge">{{ $invoices->total() }} فاتورة</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الرقم</th>
                    <th>اسم المورد</th>
                    <th>تاريخ الشراء</th>
                    <th>الحالة</th>
                    <th>الإجمالي</th>
                    <th>المتبقي</th>
                    <th>أنشئت بواسطة</th>
                    <th>تاريخ الإنشاء</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($invoices as $inv)
                <tr>
                    <td class="col-serial">{{ row_no($invoices, $loop) }}</td>
                    <td class="cell-strong">{{ $inv->number }}</td>
                    <td>{{ $inv->supplier->name ?? '-' }}</td>
                    <td>{{ optional($inv->invoice_date)->format('Y-m-d') }}</td>
                    <td><span class="badge badge-{{ invoice_status_badge($inv->status) }}">{{ invoice_status_label($inv->status) }}</span></td>
                    <td class="cell-money">{{ money($inv->total) }}</td>
                    <td class="cell-money {{ $inv->remaining > 0 ? 'is-neg' : '' }}">{{ money($inv->remaining) }}</td>
                    <td>{{ $inv->creator->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($inv->created_at)->format('H:i Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('purchases.show', $inv)" title="عرض الفاتورة" />
                            <x-print-link :href="route('purchases.print', $inv)" title="طباعة الفاتورة" />
                            @if(can('purchases.update'))
                                <x-edit-link :href="route('purchases.edit', $inv)" />
                            @endif
                            @if(can('purchases.delete'))
                                <x-delete-form :action="route('purchases.destroy', $inv)" message="هل أنت متأكد من حذف فاتورة {{ $inv->number }}؟ لا يمكن التراجع عن هذا الإجراء." title="حذف الفاتورة" />
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="cell-empty text-center text-muted"><i class="fe fe-file-text"></i>لا توجد فواتير</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$invoices" />
</div>

<div class="card mt-3">
    <div class="card-header">
        <h4 class="card-title mb-0">ملخص إجماليات فواتير المشتريات</h4>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0 invoice-summary-table">
            <tbody>
                <tr>
                    <td class="invoice-summary-label">إجمالي الفواتير المعروضة حالياً</td>
                    <td class="invoice-summary-value"><span class="invoice-summary-pill is-info">{{ money($displayedTotal) }}</span></td>
                </tr>
                <tr>
                    <td class="invoice-summary-label">الإجمالي الكلي لفواتير الشراء (غير الملغاة)</td>
                    <td class="invoice-summary-value"><span class="invoice-summary-pill is-success">{{ money($grandTotal) }}</span></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
