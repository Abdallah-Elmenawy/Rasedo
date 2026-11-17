@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الحسابات الدائنة',
        'subtitle' => 'أرصدة الموردين المستحقة',
        'export' => 'payables',
    ])
@endsection
@section('content')
<x-filter-drawer>
    <x-filter-field label="الكود" name="code" placeholder="أدخل الكود..." />
    <x-filter-field label="المورد" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="الموبايل" name="phone" placeholder="أدخل الموبايل..." />
    <x-filter-field label="الرصيد" name="balance" type="number" placeholder="أدخل الرصيد..." />
</x-filter-drawer>
<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-briefcase"></i> الموردون</h4>
        <span class="count-badge">{{ $suppliers->total() }} مورد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>الكود</th><th>المورد</th><th>الموبايل</th><th>الرصيد</th><th class="col-actions">الإجراءات</th></tr></thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td class="text-muted">{{ $s->code }}</td>
                    <td class="cell-strong"><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
                    <td dir="ltr">{{ $s->phone ?: '-' }}</td>
                    <td class="cell-money {{ $s->current_balance > 0 ? 'is-neg' : '' }}">{{ money($s->current_balance) }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('suppliers.show', $s)" title="كشف الحساب" />
                            @if(can('suppliers.update'))<x-edit-link :href="route('suppliers.edit', $s)" />@endif
                            @if(can('suppliers.delete'))<x-delete-form :action="route('suppliers.destroy', $s)" message="هل أنت متأكد من حذف هذا المورد؟ سيتم إزالة بياناته من النظام." title="حذف المورد" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-briefcase"></i>لا توجد حسابات دائنة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$suppliers" />
    <div class="card-footer report-total">
        <span>إجمالي الأرصدة الدائنة</span>
        <strong>{{ money($balanceTotal) }}</strong>
    </div>
</div>
@endsection
