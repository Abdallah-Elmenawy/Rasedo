@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'دفتر الأستاذ',
        'subtitle' => 'التقارير',
        'action' => '<a href="'.route('reports.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل التقارير</a>',
        'export' => 'reports-ledger',
    ])
@endsection
@section('content')
<x-filter-drawer>
    <div class="filter-field">
        <label>الحساب</label>
        <select name="account_id" class="form-control">
            <option value="">اختر حسابًا...</option>
            @foreach($accounts as $a)
                <option value="{{ $a->id }}" @selected(optional($account)->id == $a->id)>{{ $a->code }} — {{ $a->name }}</option>
            @endforeach
        </select>
    </div>
    <x-filter-field label="رقم القيد" name="journal_number" placeholder="أدخل رقم القيد..." />
    <x-filter-field label="البيان" name="description" placeholder="أدخل البيان..." />
    <x-filter-field label="التاريخ" name="journal_date" type="date" />
    <x-filter-field label="مدين" name="debit" type="number" placeholder="أدخل المدين..." />
    <x-filter-field label="دائن" name="credit" type="number" placeholder="أدخل الدائن..." />
</x-filter-drawer>

@if($account)
<div class="row">
    <div class="col-md-4 col-sm-6"><div class="card report-stat stat-blue"><div class="card-body"><span>إجمالي المدين</span><h3>{{ money($lines->sum('debit')) }}</h3></div></div></div>
    <div class="col-md-4 col-sm-6"><div class="card report-stat stat-purple"><div class="card-body"><span>إجمالي الدائن</span><h3>{{ money($lines->sum('credit')) }}</h3></div></div></div>
    <div class="col-md-4 col-sm-12"><div class="card report-stat stat-green"><div class="card-body"><span>الرصيد الحالي</span><h3>{{ money($account->current_balance) }}</h3></div></div></div>
</div>

<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <h4 class="card-title mb-0">{{ $account->code }} — {{ $account->name }}</h4>
        <span class="badge badge-light report-count">{{ $lines->total() }} حركة</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead><tr><th class="col-serial">#</th><th>رقم القيد</th><th>البيان</th><th>التاريخ</th><th class="text-left">مدين</th><th class="text-left">دائن</th></tr></thead>
            <tbody>
            @forelse($lines as $line)
                <tr>
                    <td class="col-serial">{{ row_no($lines, $loop) }}</td>
                    <td>{{ $line->journal->number ?? '-' }}</td>
                    <td>{{ $line->journal->description ?? '-' }}</td>
                    <td class="text-muted">{{ optional($line->journal?->journal_date ?? $line->created_at)->format('Y-m-d') }}</td>
                    <td class="text-left {{ $line->debit > 0 ? 'text-success font-weight-bold' : 'text-muted' }}">{{ money($line->debit) }}</td>
                    <td class="text-left {{ $line->credit > 0 ? 'text-danger font-weight-bold' : 'text-muted' }}">{{ money($line->credit) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">لا توجد حركات على هذا الحساب</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$lines" />
</div>
@else
<div class="card">
    <div class="card-body text-center text-muted py-5">
        <i class="fe fe-book-open d-block mb-2" style="font-size:40px"></i>
        اختر حسابًا من القائمة أعلاه لعرض حركاته
    </div>
</div>
@endif
@endsection
