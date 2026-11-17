@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الحسابات المدينة',
        'subtitle' => 'أرصدة العملاء المستحقة',
        'export' => 'receivables',
    ])
@endsection
@section('content')
<x-filter-drawer>
    <x-filter-field label="الكود" name="code" placeholder="أدخل الكود..." />
    <x-filter-field label="العميل" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="الموبايل" name="phone" placeholder="أدخل الموبايل..." />
    <x-filter-field label="الرصيد" name="balance" type="number" placeholder="أدخل الرصيد..." />
</x-filter-drawer>
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-users"></i> العملاء</h4>
        <span class="count-badge">{{ $customers->total() }} عميل</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>الكود</th><th>العميل</th><th>الموبايل</th><th>الرصيد</th><th class="col-actions">الإجراءات</th></tr></thead>
            <tbody>
            @forelse($customers as $c)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td class="text-muted">{{ $c->code }}</td>
                    <td class="cell-strong"><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                    <td dir="ltr">{{ $c->phone ?: '-' }}</td>
                    <td class="cell-money {{ $c->current_balance > 0 ? 'is-neg' : '' }}">{{ money($c->current_balance) }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('customers.show', $c)" title="كشف الحساب" />
                            @if(can('customers.update'))<x-edit-link :href="route('customers.edit', $c)" />@endif
                            @if(can('customers.delete'))<x-delete-form :action="route('customers.destroy', $c)" message="هل أنت متأكد من حذف هذا العميل؟ سيتم إزالة بياناته من النظام." title="حذف العميل" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-users"></i>لا توجد حسابات مدينة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$customers" />
    <div class="card-footer report-total">
        <span>إجمالي الأرصدة المدينة</span>
        <strong>{{ money($balanceTotal) }}</strong>
    </div>
</div>
@endsection
