@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'حسابات العملاء',
        'export' => 'customer-accounts',
    ])
@endsection
@section('content')
<x-filter-drawer>
    <x-filter-field label="العميل" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="المبيعات" name="sales" type="number" placeholder="أدخل المبيعات..." />
    <x-filter-field label="المدفوع" name="paid" type="number" placeholder="أدخل المدفوع..." />
    <x-filter-field label="الرصيد" name="balance" type="number" placeholder="أدخل الرصيد..." />
</x-filter-drawer>
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-users"></i> حسابات العملاء</h4>
        <span class="count-badge">{{ $customers->total() }} عميل</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>العميل</th>
                    <th>المبيعات</th>
                    <th>المدفوع</th>
                    <th>الرصيد</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($customers as $c)
                <tr>
                    <td class="col-serial">{{ row_no($customers, $loop) }}</td>
                    <td class="cell-strong"><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                    <td class="cell-money">{{ money($c->sales_total) }}</td>
                    <td class="cell-money is-pos">{{ money($c->paid_total) }}</td>
                    <td class="cell-money {{ $c->current_balance > 0 ? 'is-neg' : '' }}">{{ money($c->current_balance) }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('customers.show', $c)" title="كشف الحساب" />
                            <x-view-link :href="route('sales.index', ['customer_id' => $c->id])" title="فواتير العميل" icon="fe-file-text" class="btn-icon-print" />
                            @if(can('customers.delete'))<x-delete-form :action="route('customers.destroy', $c)" message="هل أنت متأكد من حذف هذا العميل؟ سيتم إزالة بياناته من النظام." title="حذف العميل" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-users"></i>لا يوجد عملاء</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$customers" />
</div>
@endsection
