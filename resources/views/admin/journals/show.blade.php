@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'قيد '.$journal->number,
        'subtitle' => optional($journal->journal_date)->format('Y-m-d'),
        'action' => '<a href="'.route('journals.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل القيود</a>',
        'exports' => [
            ['sheet' => 'journal-lines', 'params' => ['journal' => $journal->id]],
        ],
    ])
@endsection
@section('content')
<div class="card">
    @php $journalLines = paginate_collection($journal->lines, 15, 'lines_page'); @endphp
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-book"></i> {{ $journal->description ?: 'بنود القيد' }}</h4>
        <span class="count-badge">{{ $journalLines->total() }} بند</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>الكود</th><th>الحساب</th><th>مدين</th><th>دائن</th></tr></thead>
            <tbody>
            @foreach($journalLines as $line)
                <tr>
                    <td class="col-serial">{{ row_no($journalLines, $loop) }}</td>
                    <td class="text-muted">{{ $line->account->code ?? '-' }}</td>
                    <td class="cell-strong">{{ $line->account->name ?? '-' }}</td>
                    <td class="cell-money {{ $line->debit > 0 ? 'is-pos' : '' }}">{{ $line->debit > 0 ? money($line->debit) : '-' }}</td>
                    <td class="cell-money {{ $line->credit > 0 ? 'is-neg' : '' }}">{{ $line->credit > 0 ? money($line->credit) : '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$journalLines" />
    <div class="card-footer report-total">
        <span>الإجمالي</span>
        <strong>مدين {{ money($journal->lines->sum('debit')) }} &nbsp;|&nbsp; دائن {{ money($journal->lines->sum('credit')) }}</strong>
    </div>
</div>
@endsection
