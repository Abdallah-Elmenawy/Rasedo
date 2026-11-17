@extends('layouts.master')
@php
    $typeLabels = ['in' => 'إضافة', 'out' => 'صرف', 'adjust' => 'تسوية'];
@endphp
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $product->name,
        'subtitle' => 'رصيد المخزون',
        'action' => '<a href="'.route('inventory.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> أرصدة المخزون</a>',
        'exports' => [
            ['sheet' => 'inventory-movements', 'params' => ['product' => $product->id]],
        ],
    ])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4"><div class="card report-stat stat-blue"><div class="card-body"><span>الرصيد الحالي</span><h3>{{ qty($product->current_stock) }}</h3></div></div></div>
    <div class="col-md-4"><div class="card report-stat stat-orange"><div class="card-body"><span>الحد الأدنى</span><h3>{{ qty($product->min_stock) }}</h3></div></div></div>
    <div class="col-md-4"><div class="card report-stat stat-green"><div class="card-body"><span>قيمة المخزون</span><h3>{{ money($product->current_stock * $product->cost_price) }}</h3></div></div></div>
</div>
<div class="card">
    <div class="card-header"><h4 class="card-title"><i class="fe fe-activity"></i> حركات الصنف</h4></div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>النوع</th><th>الكمية</th><th>التاريخ</th><th>ملاحظات</th><th>بواسطة</th></tr></thead>
            <tbody>
            @forelse($movements as $m)
                <tr>
                    <td class="col-serial">{{ $movements->firstItem() + $loop->index }}</td>
                    <td>{{ $typeLabels[$m->type] ?? $m->type }}</td>
                    <td>{{ qty($m->qty) }}</td>
                    <td>{{ optional($m->moved_at)->format('Y-m-d') }}</td>
                    <td>{{ $m->notes ?: '—' }}</td>
                    <td>{{ $m->creator->name ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted">لا توجد حركات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$movements" />
</div>
@endsection
