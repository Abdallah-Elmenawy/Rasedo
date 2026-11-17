@extends('layouts.master')
@php
    $stock = (float) $product->current_stock;
    $min = (float) $product->min_stock;
    $cost = (float) $product->cost_price;
    $sale = (float) $product->sale_price;
    $margin = $sale - $cost;
    $marginRate = $sale > 0 ? ($margin / $sale) * 100 : 0;
    $isService = $product->type === 'service';
    $stockState = $isService ? 'ok' : ($stock <= 0 ? 'out' : ($stock <= $min ? 'low' : 'ok'));
    $stockLabel = ['ok' => 'متوفر', 'low' => 'منخفض', 'out' => 'نفد'][$stockState];
@endphp
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => $product->name,
        'action' => '<a href="'.route('products.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل المنتجات</a>'
            .(can('products.update') ? '<a href="'.route('products.edit', $product).'" class="btn btn-primary"><i class="fe fe-edit-2 ml-1"></i> تعديل</a>' : ''),
    ])
@endsection
@section('css')
<style>
.product-sheet { display: flex; flex-direction: column; gap: 16px; }
.product-hero,
.product-panel,
.product-stat {
    background: #fff;
    border: 1px solid #e6ebf4;
    border-radius: 16px;
    box-shadow: 0 8px 24px rgba(30, 78, 155, 0.05);
}
.product-hero {
    display: flex;
    align-items: center;
    gap: 18px;
    padding: 22px 24px;
    position: relative;
    overflow: hidden;
}
.product-hero::before {
    content: "";
    position: absolute;
    inset-inline: 0;
    top: 0;
    height: 4px;
    background: linear-gradient(90deg, #5B92D4, #1E4E9B);
}
.product-mark {
    width: 68px;
    height: 68px;
    flex: 0 0 68px;
    border-radius: 18px;
    display: grid;
    place-items: center;
    background: linear-gradient(135deg, #5B92D4, #1E4E9B);
    color: #fff;
    font-size: 30px;
    font-weight: 800;
}
.product-hero-main { min-width: 0; flex: 1; }
.product-name {
    margin: 0;
    font-size: 26px;
    font-weight: 800;
    color: #1f2a44;
    line-height: 1.3;
}
.product-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
.product-pill {
    display: inline-flex;
    align-items: center;
    height: 28px;
    padding: 0 12px;
    border-radius: 999px;
    background: #eef3fb;
    color: #1E4E9B;
    font-size: 13px;
    font-weight: 700;
}
.product-pill.is-on { background: rgba(16, 185, 129, 0.14); color: #059669; }
.product-pill.is-off { background: rgba(239, 68, 68, 0.12); color: #DC2626; }
.product-pill.is-low { background: rgba(245, 158, 11, 0.16); color: #B45309; }
.product-pill.is-out { background: rgba(239, 68, 68, 0.12); color: #DC2626; }
.product-codes { display: flex; flex-wrap: wrap; gap: 18px; margin-top: 14px; }
.product-codes span { display: block; color: #6c7a92; font-size: 12px; font-weight: 700; }
.product-codes strong {
    display: inline-block;
    margin-top: 2px;
    font-size: 16px;
    letter-spacing: 0.3px;
    color: #1f2a44;
    direction: ltr;
}
.product-barcode {
    height: 64px;
    width: auto;
    max-width: 220px;
    object-fit: contain;
    background: #fff;
    padding: 6px;
    border: 1px solid #e6ebf4;
    border-radius: 10px;
}
.product-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
.product-stat { padding: 16px 18px; }
.product-stat span { display: block; color: #6c7a92; font-size: 13px; font-weight: 700; }
.product-stat strong { display: block; margin-top: 6px; font-size: 22px; font-weight: 800; color: #1f2a44; }
.product-stat span.product-money,
.product-fact span.product-money {
    display: inline;
    direction: ltr;
    unicode-bidi: bidi-override;
}
.product-stat small { display: block; margin-top: 4px; color: #6c7a92; font-size: 12px; font-weight: 700; }
.product-stat.is-sale { background: linear-gradient(180deg, #f7faff 0%, #fff 100%); }
.product-stat.is-sale strong { color: #1E4E9B; }
.product-stat.is-ok strong { color: #059669; }
.product-stat.is-low strong { color: #B45309; }
.product-stat.is-out strong { color: #DC2626; }
.product-panel { padding: 8px 8px 18px; }
.product-panel h3 {
    margin: 8px 14px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid #eef2f8;
    font-size: 16px;
    font-weight: 800;
    color: #1f2a44;
}
.product-facts { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
.product-fact { padding: 16px 18px; border-bottom: 1px solid #f2f5fa; }
.product-fact span { display: block; color: #6c7a92; font-size: 12px; font-weight: 700; }
.product-fact strong { display: block; margin-top: 4px; font-size: 16px; font-weight: 800; color: #1f2a44; }
.product-note {
    margin: 8px 18px 0;
    padding-top: 14px;
    color: #4b5568;
    font-size: 15px;
    line-height: 1.8;
}
.product-note span { display: block; margin-bottom: 4px; color: #6c7a92; font-size: 12px; font-weight: 700; }
@media (max-width: 991px) {
    .product-stats, .product-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .product-barcode { display: none; }
}
@media (max-width: 575px) {
    .product-hero { align-items: flex-start; }
    .product-stats, .product-facts { grid-template-columns: 1fr; }
}
</style>
@endsection
@section('content')
<div class="product-sheet">
    <section class="product-hero">
        <div class="product-mark">{{ mb_substr($product->name, 0, 1) }}</div>
        <div class="product-hero-main">
            <h2 class="product-name">{{ $product->name }}</h2>
            <div class="product-pills">
                <span class="product-pill {{ $product->is_active ? 'is-on' : 'is-off' }}">{{ $product->is_active ? 'نشط' : 'موقوف' }}</span>
                <span class="product-pill">{{ $isService ? 'خدمة' : 'منتج' }}</span>
                <span class="product-pill">{{ $product->category->name ?? 'بدون تصنيف' }}</span>
                @unless($isService)
                    <span class="product-pill is-{{ $stockState }}">{{ $stockLabel }}</span>
                @endunless
                @if($product->track_serial)
                    <span class="product-pill">تتبع سيريال</span>
                @endif
            </div>
            <div class="product-codes">
                <div><span>SKU</span><strong>{{ $product->sku }}</strong></div>
                <div><span>الباركود</span><strong>{{ $product->scan_code ?: '—' }}</strong></div>
            </div>
        </div>
        @if($product->scan_code)
            <img class="product-barcode" src="{{ route('products.barcode', $product) }}" alt="باركود {{ $product->name }}">
        @endif
    </section>

    <section class="product-stats">
        <article class="product-stat is-sale">
            <span>سعر البيع</span>
            <strong><span class="product-money">{{ money($sale) }}</span></strong>
        </article>
        <article class="product-stat">
            <span>سعر التكلفة</span>
            <strong><span class="product-money">{{ money($cost) }}</span></strong>
        </article>
        <article class="product-stat">
            <span>هامش الوحدة</span>
            <strong><span class="product-money">{{ money($margin) }}</span></strong>
            <small>{{ number_format($marginRate, 0) }}% من سعر البيع</small>
        </article>
        <article class="product-stat is-{{ $stockState }}">
            <span>{{ $isService ? 'الوحدة' : 'المخزون الحالي' }}</span>
            <strong>{{ $isService ? ($product->unit ?: '—') : qty($stock) }}</strong>
            @unless($isService)
                <small>الحد الأدنى {{ qty($min) }}</small>
            @endunless
        </article>
    </section>

    <section class="product-panel">
        <h3>بيانات الصنف</h3>
        <div class="product-facts">
            <div class="product-fact"><span>الوحدة</span><strong>{{ $product->unit ?: '—' }}</strong></div>
            <div class="product-fact"><span>قطع العلبة</span><strong>{{ $product->pieces_per_box ?: '—' }}</strong></div>
            <div class="product-fact"><span>الضريبة</span><strong>{{ number_format((float) $product->tax_rate, 2) }}%</strong></div>
            <div class="product-fact"><span>قيمة المخزون</span><strong><span class="product-money">{{ $isService ? '—' : money($stock * $cost) }}</span></strong></div>
            <div class="product-fact"><span>الحد الأدنى</span><strong>{{ $isService ? '—' : qty($min) }}</strong></div>
            <div class="product-fact"><span>التتبع</span><strong>{{ $product->track_serial ? 'سيريال لكل قطعة' : 'بدون سيريال' }}</strong></div>
        </div>
        <div class="product-note">
            <span>الوصف</span>
            {{ $product->description ?: 'لا يوجد وصف لهذا الصنف.' }}
        </div>
    </section>
</div>
@endsection
