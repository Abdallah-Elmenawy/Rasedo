@extends('layouts.master')

@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'ملخص المبيعات',
        'subtitle' => 'وردية اليوم، حركة البيع، وصافي الربح.',
        'action' => '<a class="btn btn-primary" href="'.route('pos.sell').'">شاشة البيع</a>',
    ])
@endsection

@section('content')
@php
    $shiftOpen = is_array($shift);
    $openedAt = $shiftOpen ? \Illuminate\Support\Carbon::parse($shift['opened_at']) : null;
    $morning = $openedAt ? $openedAt->hour < 16 : true;
    $report = session('pos_shift_report');
@endphp
<div class="pos-shell">
    <div class="pos-kpis">
        <div class="pos-shift card">
            <div class="pos-shift-top">
                <div>
                    <span class="pos-kpi-label">صافي المبيعات</span>
                    <strong class="pos-shift-amount pos-money">{{ money($shiftPreview['sales'] ?? $stats['today_net']) }}</strong>
                    <span class="pos-kpi-hint">اليوم</span>
                </div>
                <span class="pos-shift-sun"><i class="fe fe-sun"></i></span>
            </div>
            <div class="pos-shift-status">
                <div>
                    <span class="pos-kpi-label">حالة الشفت</span>
                    @if($shiftOpen)
                        <span class="pos-badge is-open"><i></i> مفتوح</span>
                    @else
                        <span class="pos-badge is-closed"><i></i> مغلق</span>
                    @endif
                </div>
                <div class="pos-shift-name">
                    {{ $morning ? 'شفت الصباح' : 'شفت المساء' }}
                    <small><i class="fe fe-sun"></i> {{ $morning ? '08:00 - 16:00' : '16:00 - 00:00' }}</small>
                </div>
            </div>
            <div class="pos-shift-foot">
                <div>
                    <span class="pos-kpi-label">مندوب الكاشير</span>
                    <strong>{{ auth()->user()->name }}</strong>
                </div>
                <div>
                    <span class="pos-kpi-label">بداية الشفت</span>
                    <strong>{{ $openedAt ? $openedAt->format('H:i') : '—' }}</strong>
                </div>
            </div>
        </div>

        <div class="pos-kpi-grid">
            <div class="pos-kpi card">
                <span class="pos-kpi-icon"><i class="fe fe-file-text"></i></span>
                <span class="pos-kpi-label">عدد الفواتير</span>
                <strong>{{ $stats['today_count'] }}</strong>
                <span class="pos-kpi-hint">اليوم</span>
            </div>
            <div class="pos-kpi card">
                <span class="pos-kpi-icon is-info"><i class="fe fe-bar-chart-2"></i></span>
                <span class="pos-kpi-label">متوسط الفاتورة</span>
                <strong class="pos-money">{{ money($stats['today_average']) }}</strong>
                <span class="pos-kpi-hint">اليوم</span>
            </div>
            <div class="pos-kpi card">
                <span class="pos-kpi-icon is-success"><i class="fe fe-trending-up"></i></span>
                <span class="pos-kpi-label">إجمالي المبيعات</span>
                <strong class="pos-money">{{ money($stats['today_sales']) }}</strong>
                <span class="pos-kpi-hint">اليوم <bdi dir="ltr">{{ $stats['today_sales_delta'] >= 0 ? '+' : '' }}{{ $stats['today_sales_delta'] }}%</bdi></span>
            </div>
            <div class="pos-kpi card">
                <span class="pos-kpi-icon is-warn"><i class="fe fe-dollar-sign"></i></span>
                <span class="pos-kpi-label">صافي المبيعات</span>
                <strong class="pos-money">{{ money($stats['today_net']) }}</strong>
                <span class="pos-kpi-hint">بعد المرتجعات</span>
            </div>
            <div class="pos-kpi card">
                <span class="pos-kpi-icon"><i class="fe fe-pie-chart"></i></span>
                <span class="pos-kpi-label">صافي الربح اليومي</span>
                <strong class="pos-money">{{ money($stats['today_profit']) }}</strong>
                <span class="pos-kpi-hint">اليوم</span>
            </div>
        </div>
    </div>

    @if($report)
        <div class="pos-report card">
            <strong>مراجعة إغلاق الوردية — {{ $report['treasury_name'] }}</strong>
            <span>فواتير {{ $report['invoices'] }}</span>
            <span>مبيعات {{ money($report['sales']) }}</span>
            <span>نقدي {{ money($report['cash']) }}</span>
            <span>إلكتروني {{ money($report['card']) }}</span>
            <span>المتوقع {{ money($report['expected_cash']) }}</span>
            <span>الفعلي {{ money($report['actual_cash']) }}</span>
            <span>الفرق {{ money($report['difference']) }}</span>
        </div>
    @endif
</div>
@endsection

