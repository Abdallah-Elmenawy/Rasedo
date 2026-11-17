@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'مرتجع '.$return->number,
        'subtitle' => 'مرتجعات المشتريات',
        'action' => '<a href="'.route('purchase-returns.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل المرتجعات</a>',
        'exports' => [
            ['sheet' => 'purchase-return-items', 'params' => ['purchase_return' => $return->id]],
        ],
    ])
@endsection
@section('content')
<div class="row">
    <div class="col-md-4 col-sm-6"><div class="card report-stat stat-blue"><div class="card-body"><span>المورد</span><h3 class="tx-18">{{ $return->supplier->name ?? '-' }}</h3><small class="text-muted">{{ optional($return->return_date)->format('Y-m-d') }}</small></div></div></div>
    <div class="col-md-4 col-sm-6"><div class="card report-stat stat-purple"><div class="card-body"><span>الفاتورة الأصلية</span><h3 class="tx-18">@if($return->invoice)<a href="{{ route('purchases.show', $return->invoice) }}">{{ $return->invoice->number }}</a>@else - @endif</h3></div></div></div>
    <div class="col-md-4 col-sm-12"><div class="card report-stat stat-red"><div class="card-body"><span>إجمالي المرتجع</span><h3>{{ money($return->total) }}</h3></div></div></div>
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-list"></i> بنود المرتجع</h4>
        <span class="badge badge-{{ invoice_status_badge($return->status) }}">{{ invoice_status_label($return->status) }}</span>
    </div>
    <div class="table-responsive">
        @php $returnItems = paginate_collection($return->items, 15, 'items_page'); @endphp
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>المنتج</th><th>الكمية</th><th>السعر</th><th>الإجمالي</th></tr></thead>
            <tbody>
            @foreach($returnItems as $item)
                <tr>
                    <td class="col-serial">{{ row_no($returnItems, $loop) }}</td>
                    <td class="cell-strong">{{ $item->product->name ?? '-' }}</td>
                    <td>{{ $item->qty }}</td>
                    <td class="cell-money">{{ money($item->unit_cost) }}</td>
                    <td class="cell-money">{{ money($item->total) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$returnItems" />
    <div class="card-footer table-card-footer">
        <div class="report-total flex-grow-1"><span>الإجمالي</span><strong>{{ money($return->total) }}</strong></div>
        @if($return->status==='draft' && can('purchases.update'))
            <a class="btn btn-light" href="{{ route('purchase-returns.edit', $return) }}"><i class="fe fe-edit-2 ml-1"></i> تعديل</a>
            <form method="post" action="{{ route('purchase-returns.confirm', $return) }}">@csrf<button class="btn btn-success"><i class="fe fe-check ml-1"></i> تأكيد المرتجع</button></form>
        @endif
    </div>
</div>
@endsection
