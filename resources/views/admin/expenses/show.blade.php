@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $entry->number,
        'subtitle' => $entry->type === 'expense' ? 'مصروف' : 'إيراد',
        'action' => '<a href="'.route('expenses.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل العمليات</a>'
            .(can('expenses.update') ? '<a href="'.route('expenses.edit', $entry).'" class="btn btn-primary"><i class="fe fe-edit-2 ml-1"></i> تعديل</a>' : ''),
    ])
@endsection
@section('content')
<div class="card">
    <div class="card-body">
        <div class="row text-right">
            <div class="col-md-4 mb-3"><span class="text-muted d-block">النوع</span><strong>{{ $entry->type === 'expense' ? 'مصروف' : 'إيراد' }}</strong></div>
            <div class="col-md-4 mb-3"><span class="text-muted d-block">التصنيف</span><strong>{{ $entry->category->name ?? '—' }}</strong></div>
            <div class="col-md-4 mb-3"><span class="text-muted d-block">الخزينة</span><strong>{{ $entry->treasury->name ?? '—' }}</strong></div>
            <div class="col-md-4 mb-3"><span class="text-muted d-block">المبلغ</span><strong>{{ money($entry->amount) }}</strong></div>
            <div class="col-md-4 mb-3"><span class="text-muted d-block">التاريخ</span><strong>{{ optional($entry->entry_date)->format('Y-m-d') }}</strong></div>
            <div class="col-md-4 mb-3"><span class="text-muted d-block">أضافها</span><strong>{{ $entry->creator->name ?? '—' }}</strong></div>
            <div class="col-12"><span class="text-muted d-block">البيان</span><strong>{{ $entry->description ?: '—' }}</strong></div>
        </div>
    </div>
</div>
@endsection
