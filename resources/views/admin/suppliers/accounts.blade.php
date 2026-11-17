@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'حسابات الموردين',
        'export' => 'supplier-accounts',
    ])
@endsection
@section('content')
<x-filter-drawer>
    <x-filter-field label="المورد" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="المشتريات" name="purchases" type="number" placeholder="أدخل المشتريات..." />
    <x-filter-field label="المدفوع" name="paid" type="number" placeholder="أدخل المدفوع..." />
    <x-filter-field label="الرصيد" name="balance" type="number" placeholder="أدخل الرصيد..." />
</x-filter-drawer>
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-briefcase"></i> حسابات الموردين</h4>
        <span class="count-badge">{{ $suppliers->total() }} مورد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>المورد</th>
                    <th>المشتريات</th>
                    <th>المدفوع</th>
                    <th>الرصيد</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td class="col-serial">{{ row_no($suppliers, $loop) }}</td>
                    <td class="cell-strong"><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
                    <td class="cell-money">{{ money($s->purchases_total) }}</td>
                    <td class="cell-money is-pos">{{ money($s->paid_total) }}</td>
                    <td class="cell-money {{ $s->current_balance > 0 ? 'is-neg' : '' }}">{{ money($s->current_balance) }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('suppliers.show', $s)" title="كشف الحساب" />
                            <x-view-link :href="route('purchases.index', ['supplier_id' => $s->id])" title="فواتير المورد" icon="fe-file-text" class="btn-icon-print" />
                            @if(can('suppliers.delete'))<x-delete-form :action="route('suppliers.destroy', $s)" message="هل أنت متأكد من حذف هذا المورد؟ سيتم إزالة بياناته من النظام." title="حذف المورد" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-briefcase"></i>لا يوجد موردون</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$suppliers" />
</div>
@endsection
