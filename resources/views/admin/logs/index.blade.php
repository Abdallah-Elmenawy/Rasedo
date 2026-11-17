@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'سجل العمليات',
        'export' => 'logs',
    ])
@endsection
@section('content')
@php
    $moduleLabels = [
        'customers' => 'العملاء',
        'suppliers' => 'الموردون',
        'products' => 'المنتجات',
        'sales' => 'المبيعات',
        'purchases' => 'المشتريات',
        'treasury' => 'الخزينة',
        'expenses' => 'المصروفات',
        'inventory' => 'المخزون',
        'users' => 'المستخدمون',
        'accounting' => 'الحسابات',
    ];
    $actionLabels = [
        'create' => 'إضافة',
        'update' => 'تعديل',
        'delete' => 'حذف',
        'confirm' => 'تأكيد',
        'receipt' => 'تحصيل',
        'payment' => 'صرف',
        'transfer' => 'تحويل',
        'return' => 'مرتجع',
        'in' => 'إضافة مخزون',
        'out' => 'صرف مخزون',
        'adjust' => 'تسوية',
        'expense' => 'مصروف',
        'revenue' => 'إيراد',
    ];
    $actionBadges = [
        'create' => 'success',
        'update' => 'info',
        'delete' => 'danger',
        'confirm' => 'primary',
        'receipt' => 'success',
        'payment' => 'warning',
        'transfer' => 'secondary',
        'return' => 'warning',
        'in' => 'success',
        'out' => 'danger',
        'adjust' => 'info',
        'expense' => 'danger',
        'revenue' => 'success',
    ];
@endphp

<x-filter-drawer>
    <x-filter-field label="الوصف" name="description" placeholder="أدخل الوصف..." />
    <x-filter-field label="الوحدة" name="module" type="select" :options="$filters['modules']" />
    <x-filter-field label="الإجراء" name="action" type="select" :options="$filters['actions']" />
    <x-filter-field label="المستخدم" name="user_id" type="select" :options="$filters['users']" />
    <x-filter-field label="التاريخ" name="created_on" type="date" />
</x-filter-drawer>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-clock"></i> سجل العمليات</h4>
        <span class="count-badge">{{ $logs->total() }} عملية</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الوصف</th>
                    <th>الوحدة</th>
                    <th>الإجراء</th>
                    <th>المستخدم</th>
                    <th>التاريخ</th>
                </tr>
            </thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td class="col-serial">{{ row_no($logs, $loop) }}</td>
                    <td class="text-right">{{ $log->description }}</td>
                    <td>{{ $moduleLabels[$log->module] ?? $log->module }}</td>
                    <td>
                        <span class="badge badge-{{ $actionBadges[$log->action] ?? 'light' }}">
                            {{ $actionLabels[$log->action] ?? $log->action }}
                        </span>
                    </td>
                    <td>{{ $log->user->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($log->created_at)->format('H:i Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-clock"></i>لا توجد عمليات مسجلة</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$logs" />
</div>
@endsection
