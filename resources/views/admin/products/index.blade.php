@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'المنتجات',
        'action' => can('products.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addProductModal"><i class="fe fe-plus ml-1"></i> إضافة منتج</button>' : '',
        'export' => 'products',
    ])
@endsection
@section('content')
@php $openAddModal = $errors->any(); @endphp

<x-filter-drawer>
    <x-filter-field label="SKU" name="sku" placeholder="أدخل SKU..." />
    <x-filter-field label="الاسم" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="التصنيف" name="category_id" type="select" :options="$filters['categories']" />
    <x-filter-field label="النوع" name="type" type="select" :options="$filters['types']" />
    <x-filter-field label="التكلفة" name="cost_price" type="number" placeholder="أدخل التكلفة..." />
    <x-filter-field label="البيع" name="sale_price" type="number" placeholder="أدخل سعر البيع..." />
    <x-filter-field label="المخزون" name="current_stock" type="number" placeholder="أدخل المخزون..." />
    <x-filter-field label="الحالة" name="stock_status" type="select" :options="$filters['statuses']" />
</x-filter-drawer>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-package"></i> المنتجات</h4>
        <span class="count-badge">{{ $products->total() }} منتج</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>SKU</th>
                    <th>الباركود</th>
                    <th>الاسم</th>
                    <th>التصنيف</th>
                    <th>النوع</th>
                    <th>التكلفة</th>
                    <th>البيع</th>
                    <th>المخزون</th>
                    <th>الحالة</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                <tr>
                    <td class="col-serial">{{ row_no($products, $loop) }}</td>
                    <td class="text-muted">{{ $p->sku }}</td>
                    <td>{{ $p->scan_code ?: '—' }}</td>
                    <td class="cell-strong">{{ $p->name }}</td>
                    <td>{{ $p->category->name ?? '-' }}</td>
                    <td><span class="badge badge-{{ $p->type === 'service' ? 'info' : 'light' }}">{{ $p->type === 'service' ? 'خدمة' : 'منتج' }}</span></td>
                    <td class="cell-money">{{ money($p->cost_price) }}</td>
                    <td class="cell-money is-pos">{{ money($p->sale_price) }}</td>
                    <td>{{ qty($p->current_stock) }}</td>
                    <td>
                        @if($p->type !== 'product')
                            <span class="text-muted">—</span>
                        @elseif($p->current_stock <= 0)
                            <span class="badge badge-danger">نفد</span>
                        @elseif($p->isLowStock())
                            <span class="badge badge-warning">منخفض</span>
                        @else
                            <span class="badge badge-success">متوفر</span>
                        @endif
                    </td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <a href="{{ route('products.label', $p) }}" class="btn btn-icon-action btn-icon-print" title="طباعة الباركود" target="_blank"><i class="fe fe-printer"></i></a>
                            <x-view-link :href="route('products.show', $p)" title="عرض المنتج" />
                            @if(can('products.update'))<x-edit-link :href="route('products.edit', $p)" />@endif
                            @if(can('products.delete'))<x-delete-form :action="route('products.destroy', $p)" message="هل أنت متأكد من حذف هذا المنتج؟ لا يمكن التراجع عن هذا الإجراء." title="حذف المنتج" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="11" class="cell-empty text-center text-muted"><i class="fe fe-package"></i>لا توجد منتجات</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$products" />
</div>

<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('products.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addProductModalTitle">إضافة منتج</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label for="add_sku">كود المنتج</label>
                            <input id="add_sku" name="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" placeholder="امسح الباركود أو اكتبه" autocomplete="off" required>
                            <small class="text-muted">وجّه جهاز الاسكانر على الكود، الرقم يتسجل هنا فورًا.</small>
                            @error('sku')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_name">الاسم</label>
                            <input id="add_name" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                            @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_category">التصنيف</label>
                            <select id="add_category" name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                <option value="">—</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_type">النوع</label>
                            <select id="add_type" name="type" class="form-control">
                                <option value="product" @selected(old('type', 'product') === 'product')>منتج</option>
                                <option value="service" @selected(old('type') === 'service')>خدمة</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_unit">الوحدة</label>
                            <input id="add_unit" name="unit" class="form-control" value="{{ old('unit', 'قطعة') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_pieces_per_box">عدد القطع داخل العلبة</label>
                            <input id="add_pieces_per_box" name="pieces_per_box" type="number" min="1" step="1" class="form-control" value="{{ old('pieces_per_box') }}" placeholder="مثال: 12">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_cost_price">سعر التكلفة</label>
                            <input id="add_cost_price" name="cost_price" type="number" step="0.01" class="form-control @error('cost_price') is-invalid @enderror" value="{{ old('cost_price') }}">
                            @error('cost_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_sale_price">سعر البيع</label>
                            <input id="add_sale_price" name="sale_price" type="number" step="0.01" class="form-control @error('sale_price') is-invalid @enderror" value="{{ old('sale_price') }}" required>
                            @error('sale_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_min_stock">الحد الأدنى</label>
                            <input id="add_min_stock" name="min_stock" type="number" step="0.001" class="form-control" value="{{ old('min_stock') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label for="add_current_stock">المخزون الحالي</label>
                            <input id="add_current_stock" name="current_stock" type="number" step="0.001" class="form-control" value="{{ old('current_stock') }}">
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="product-check" for="add_is_active">
                                <input id="add_is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', '1'))>
                                نشط
                            </label>
                        </div>
                        <div class="col-md-6 form-group">
                            <label class="product-check" for="add_track_serial">
                                <input id="add_track_serial" type="checkbox" name="track_serial" value="1" @checked(old('track_serial'))>
                                تتبع سيريال لكل قطعة
                            </label>
                        </div>
                        <div class="col-md-12 form-group">
                            <label for="add_description">الوصف</label>
                            <textarea id="add_description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">رجوع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
<script>
$(function () {
    var modal = $('#addProductModal');
    @if($openAddModal)
    modal.modal('show');
    @endif
    modal.on('shown.bs.modal', function () {
        var sku = document.getElementById('add_sku');
        if (sku) sku.focus();
    });
    modal.on('keydown', function (event) {
        if (event.key !== 'Enter' || event.target.id !== 'add_sku') return;
        event.preventDefault();
        var name = document.getElementById('add_name');
        if (name) name.focus();
    });
});
</script>
@endsection
