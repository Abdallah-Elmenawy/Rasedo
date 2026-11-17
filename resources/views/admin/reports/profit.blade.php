@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الأرباح والخسائر',
        'subtitle' => 'التقارير',
        'action' => '<a href="'.route('reports.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل التقارير</a>',
        'export' => 'reports-profit',
    ])
@endsection
@section('content')
@php $net = $sales + $revenues - $purchases - $expenses; @endphp
<x-filter-drawer>
    <x-filter-field label="من تاريخ" name="from" type="date" :value="$from" />
    <x-filter-field label="إلى تاريخ" name="to" type="date" :value="$to" />
    <x-filter-field label="البند" name="item" type="select" :options="['المبيعات' => 'المبيعات', 'إيرادات أخرى' => 'إيرادات أخرى', 'المشتريات' => 'المشتريات', 'المصروفات' => 'المصروفات']" />
</x-filter-drawer>

<div class="row">
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-green"><div class="card-body"><span>المبيعات</span><h3>{{ money($sales) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-teal"><div class="card-body"><span>إيرادات أخرى</span><h3>{{ money($revenues) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-orange"><div class="card-body"><span>المشتريات</span><h3>{{ money($purchases) }}</h3></div></div></div>
    <div class="col-md-3 col-sm-6"><div class="card report-stat stat-red"><div class="card-body"><span>المصروفات</span><h3>{{ money($expenses) }}</h3></div></div></div>
</div>

@php
    $incomeRows = collect([
        ['المبيعات', $sales, false],
        ['إيرادات أخرى', $revenues, false],
        ['المشتريات', $purchases, true],
        ['المصروفات', $expenses, true],
    ])->when(request('item'), fn ($rows) => $rows->where(0, request('item')))->values();
@endphp
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">قائمة الدخل</h4>
        <span class="badge badge-light report-count">{{ $incomeRows->count() }} {{ $incomeRows->count() === 1 ? 'بند' : 'بنود' }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover income-sheet mb-0">
            <thead><tr><th class="col-serial">#</th><th>البند</th><th class="col-amount">المبلغ</th></tr></thead>
            <tbody>
            @foreach($incomeRows as $row)
                <tr>
                    <td class="col-serial">{{ $loop->iteration }}</td>
                    <td>{{ $row[0] }}</td>
                    <td class="col-amount">
                        <span class="amount-figure {{ $row[2] ? 'is-neg' : 'is-pos' }}">{{ money($row[1]) }}</span>
                    </td>
                </tr>
            @endforeach
            </tbody>
            <tfoot>
            <tr class="{{ $net >= 0 ? 'is-profit' : 'is-loss' }}">
                <td class="col-serial"></td>
                <td class="income-label">{{ $net >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}</td>
                <td class="col-amount">
                    <span class="amount-figure {{ $net >= 0 ? 'is-pos' : 'is-neg' }}">{{ money(abs($net)) }}</span>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
