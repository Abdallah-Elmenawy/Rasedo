@extends('layouts.master')
@php
    $reportSheets = [
        'reports.sales' => 'reports-sales',
        'reports.purchases' => 'reports-purchases',
        'reports.inventory' => 'reports-inventory',
        'reports.customers' => 'reports-customers',
        'reports.suppliers' => 'reports-suppliers',
        'reports.expenses' => 'reports-expenses',
    ];
    $exportSheet = $reportSheets[optional(request()->route())->getName()] ?? 'reports-sales';
@endphp
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $title,
        'subtitle' => 'التقارير',
        'action' => '<a href="'.route('reports.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل التقارير</a>',
        'export' => $exportSheet,
    ])
@endsection
@section('content')
@php
    $sourceRows = collect($rows);
    $gridFilters = [];
    foreach ($headers as $index => $header) {
        $gridFilters[$index] = $sourceRows
            ->map(fn ($row) => (string) ($row[$index] ?? ''))
            ->filter(fn ($value) => $value !== '' && $value !== '-')
            ->unique()
            ->sort()
            ->mapWithKeys(fn ($value) => [$value => $value]);
    }
    $activeColumns = collect($headers)->keys()->filter(fn ($index) => request()->filled('col_'.$index));
    if ($activeColumns->isNotEmpty()) {
        $rows = $sourceRows->filter(function ($row) use ($activeColumns) {
            foreach ($activeColumns as $index) {
                if ((string) ($row[$index] ?? '') !== (string) request('col_'.$index)) {
                    return false;
                }
            }

            return true;
        })->values();
    }
    $rows = paginate_collection($rows);
@endphp
<x-filter-drawer>
    @if($from)
        <x-filter-field label="من تاريخ" name="from" type="date" :value="$from" />
        <x-filter-field label="إلى تاريخ" name="to" type="date" :value="$to" />
    @endif
    @foreach($headers as $index => $header)
        <x-filter-field :label="$header" :name="'col_'.$index" type="select" :options="$gridFilters[$index]" />
    @endforeach
</x-filter-drawer>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">{{ $title }}</h4>
        <span class="badge badge-light report-count">{{ $rows->total() }} سجل</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th>@foreach($headers as $h)<th>{{ $h }}</th>@endforeach</tr></thead>
            <tbody>
            @forelse($rows as $row)
                <tr><td class="col-serial">{{ row_no($rows, $loop) }}</td>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>
            @empty
                <tr><td colspan="{{ count($headers) + 1 }}" class="text-center text-muted py-4">لا توجد بيانات لهذه الفترة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$rows" />
    <div class="card-footer report-total">
        <span>الإجمالي</span>
        <strong>{{ $total }}</strong>
    </div>
</div>
@endsection
