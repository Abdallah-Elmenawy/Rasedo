@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'المخزون',
        'action' => can('inventory.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addStockModal"><i class="fe fe-plus ml-1"></i> حركة مخزون يدوية</button>' : '',
        'exports' => [
            ['sheet' => 'inventory', 'label' => 'Excel الأرصدة'],
            ['sheet' => 'inventory-movements', 'label' => 'Excel الحركات'],
        ],
    ])
@endsection
@section('content')
<div class="card mb-3">
    <div class="card-body">
        <label for="stockScan">مسح الصنف</label>
        <input id="stockScan" class="form-control" placeholder="باركود أو QR أو سيريال" autocomplete="off">
        <div id="stockScanResult" class="stock-scan-result" hidden></div>
    </div>
</div>
@php
    $openAddModal = old('_form') === 'inventory';
    $typeLabels = ['in' => 'إضافة', 'out' => 'صرف', 'adjust' => 'تسوية'];
@endphp

@php $typeBadges = ['in' => 'success', 'out' => 'danger', 'adjust' => 'info']; @endphp

<x-filter-drawer>
    <x-filter-field label="المنتج" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="المخزون" name="current_stock" type="number" placeholder="أدخل المخزون..." />
    <x-filter-field label="الحد الأدنى" name="min_stock" type="number" placeholder="أدخل الحد الأدنى..." />
    <x-filter-field label="القيمة" name="stock_value" type="number" placeholder="أدخل القيمة..." />
    <x-filter-field label="الحالة" name="stock_status" type="select" :options="$filters['statuses']" />
</x-filter-drawer>

<div class="card mb-3">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-package"></i> أرصدة المنتجات</h4>
        <span class="count-badge">{{ $products->total() }} منتج</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>المنتج</th>
                    <th>المخزون</th>
                    <th>الحد الأدنى</th>
                    <th>القيمة</th>
                    <th>الحالة</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                <tr>
                    <td class="col-serial">{{ row_no($products, $loop) }}</td>
                    <td class="cell-strong">{{ $p->name }}</td>
                    <td class="cell-strong">{{ qty($p->current_stock) }}</td>
                    <td class="text-muted">{{ qty($p->min_stock) }}</td>
                    <td class="cell-money">{{ money($p->current_stock * $p->cost_price) }}</td>
                    <td>
                        @if($p->current_stock <= 0)
                            <span class="badge badge-danger">نفد</span>
                        @elseif($p->isLowStock())
                            <span class="badge badge-warning">منخفض</span>
                        @else
                            <span class="badge badge-success">متوفر</span>
                        @endif
                    </td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('inventory.show', $p)" title="عرض الرصيد والحركات" />
                            @if(can('products.update'))<x-edit-link :href="route('products.edit', $p)" title="تعديل المنتج" />@endif
                            @if(can('products.delete'))<x-delete-form :action="route('products.destroy', $p)" message="هل أنت متأكد من حذف هذا المنتج؟ لا يمكن التراجع عن هذا الإجراء." title="حذف المنتج" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="cell-empty text-center text-muted"><i class="fe fe-package"></i>لا توجد منتجات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$products" />
</div>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-activity"></i> آخر الحركات</h4>
        <span class="count-badge">{{ $movements->total() }} حركة</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>المنتج</th>
                    <th>النوع</th>
                    <th>الكمية</th>
                    <th>المستخدم</th>
                    <th>تاريخ التسجيل</th>
                </tr>
            </thead>
            <tbody>
            @forelse($movements as $m)
                <tr>
                    <td class="col-serial">{{ row_no($movements, $loop) }}</td>
                    <td class="cell-strong">{{ $m->product->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $typeBadges[$m->type] ?? 'light' }}">{{ $typeLabels[$m->type] ?? $m->type }}</span></td>
                    <td class="cell-strong">{{ $m->qty }}</td>
                    <td>{{ $m->creator->name ?? '-' }}</td>
                    <td class="text-muted">{{ optional($m->created_at)->format('H:i Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="cell-empty text-center text-muted"><i class="fe fe-activity"></i>لا توجد حركات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$movements" />
</div>

<div class="modal fade" id="addStockModal" tabindex="-1" role="dialog" aria-labelledby="addStockModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('inventory.store') }}">
                @csrf
                <input type="hidden" name="_form" value="inventory">
                <div class="modal-header">
                    <h5 class="modal-title" id="addStockModalTitle">حركة مخزون يدوية</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    @if($formProducts->isEmpty())
                        <div class="alert alert-warning mb-0">لا توجد منتجات يمكن تحريك مخزونها.</div>
                    @else
                        <div class="form-group">
                            <label for="stock_product_id">المنتج</label>
                            <select id="stock_product_id" name="product_id" class="form-control @error('product_id') is-invalid @enderror" required>
                                <option value="">اختر المنتج</option>
                                @foreach($formProducts as $p)
                                    <option value="{{ $p->id }}" @selected(old('product_id') == $p->id)>{{ $p->name }} ({{ qty($p->current_stock) }})</option>
                                @endforeach
                            </select>
                            @error('product_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group">
                            <label for="stock_type">نوع الحركة</label>
                            <select id="stock_type" name="type" class="form-control">
                                <option value="in" @selected(old('type') === 'in')>إضافة</option>
                                <option value="out" @selected(old('type') === 'out')>صرف</option>
                                <option value="adjust" @selected(old('type') === 'adjust')>تسوية</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="stock_qty">الكمية</label>
                            <input id="stock_qty" name="qty" type="number" step="0.001" class="form-control @error('qty') is-invalid @enderror" value="{{ old('qty') }}" required>
                            @error('qty')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="form-group mb-0">
                            <label for="stock_notes">ملاحظات</label>
                            <textarea id="stock_notes" name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    @endif
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    @if($formProducts->isNotEmpty())
                        <button type="submit" class="btn btn-primary">تسجيل</button>
                    @endif
                    <button type="button" class="btn btn-light" data-dismiss="modal">رجوع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
document.getElementById('stockScan').addEventListener('keydown', function (e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    var code = e.target.value.trim();
    var box = document.getElementById('stockScanResult');
    if (!code) return;
    fetch(@json(route('products.scan')) + '?code=' + encodeURIComponent(code), {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (response) {
        return response.json().then(function (data) { return { ok: response.ok, data: data }; });
    }).then(function (result) {
        box.hidden = false;
        if (!result.ok || !result.data.product) {
            box.textContent = (result.data && result.data.message) || 'لا يوجد صنف بهذا الكود.';
            return;
        }
        var p = result.data.product;
        box.innerHTML = '<strong>' + p.name + '</strong><span>الكود: ' + (p.scan_code || '') + '</span><span>المخزون: ' + p.stock + '</span><span>السعر: ' + p.price + '</span>' + (p.serial ? '<span>السيريال: ' + p.serial + '</span>' : '');
    });
});
</script>
@if($openAddModal)
<script>
$(function () { $('#addStockModal').modal('show'); });
</script>
@endif
@endsection
