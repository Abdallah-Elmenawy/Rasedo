@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'القيود المحاسبية',
        'action' => can('accounting.create') ? '<a href="'.route('journals.create').'" class="btn btn-primary"><i class="fe fe-plus ml-1"></i> قيد جديد</a>' : '',
        'export' => 'journals',
    ])
@endsection
@section('content')
<x-filter-drawer>
    <x-filter-field label="الرقم" name="number" placeholder="أدخل رقم القيد..." />
    <x-filter-field label="التاريخ" name="journal_date" type="date" />
    <x-filter-field label="الوصف" name="description" placeholder="أدخل الوصف..." />
    <x-filter-field label="المستخدم" name="created_by" type="select" :options="$filters['creators']" />
</x-filter-drawer>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-book"></i> القيود</h4>
        <span class="count-badge">{{ $journals->total() }} قيد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الرقم</th>
                    <th>التاريخ</th>
                    <th>الوصف</th>
                    <th>المستخدم</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($journals as $j)
                <tr>
                    <td class="col-serial">{{ row_no($journals, $loop) }}</td>
                    <td class="cell-strong"><a href="{{ route('journals.show', $j) }}">{{ $j->number }}</a></td>
                    <td>{{ optional($j->journal_date)->format('Y-m-d') }}</td>
                    <td>{{ $j->description ?: '-' }}</td>
                    <td>{{ $j->creator->name ?? '-' }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('journals.show', $j)" title="عرض القيد" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-book"></i>لا توجد قيود</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$journals" />
</div>
@endsection
