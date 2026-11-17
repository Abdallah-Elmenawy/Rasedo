@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'الموردون',
        'action' => can('suppliers.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addSupplierModal"><i class="fe fe-plus ml-1"></i> إضافة مورد جديد</button>' : '',
        'export' => 'suppliers',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'supplier'; @endphp

<x-filter-drawer>
    <x-filter-field label="الكود" name="code" placeholder="أدخل الكود..." />
    <x-filter-field label="اسم المورد" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="الموبايل" name="phone" placeholder="أدخل الموبايل..." />
    <x-filter-field label="العنوان" name="address" placeholder="أدخل العنوان..." />
    <x-filter-field label="السجل التجاري" name="tax_number" placeholder="أدخل السجل التجاري..." />
    <x-filter-field label="الرصيد" name="balance" type="number" placeholder="أدخل الرصيد..." />
    <x-filter-field label="أضيف بواسطة" name="created_by" type="select" :options="$filters['creators']" />
    <x-filter-field label="تاريخ الإضافة" name="created_on" type="date" />
</x-filter-drawer>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-briefcase"></i> الموردون</h4>
        <span class="count-badge">{{ $suppliers->total() }} مورد</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الكود</th>
                    <th>اسم المورد</th>
                    <th>الموبايل</th>
                    <th>العنوان</th>
                    <th>السجل التجاري</th>
                    <th>الرصيد</th>
                    <th>أضيف بواسطة</th>
                    <th>تاريخ الإضافة</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($suppliers as $s)
                <tr>
                    <td class="col-serial">{{ row_no($suppliers, $loop) }}</td>
                    <td class="text-muted">{{ $s->code }}</td>
                    <td class="cell-strong"><a href="{{ route('suppliers.show', $s) }}">{{ $s->name }}</a></td>
                    <td dir="ltr">{{ $s->phone ?: '-' }}</td>
                    <td>{{ $s->address ?: '-' }}</td>
                    <td>{{ $s->tax_number ?: '-' }}</td>
                    <td class="cell-money {{ $s->current_balance > 0 ? 'is-neg' : '' }}">{{ money($s->current_balance) }}</td>
                    <td>{{ $s->creator->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($s->created_at)->format('Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('suppliers.show', $s)" title="كشف الحساب" />
                            <x-view-link :href="route('purchases.index', ['supplier_id' => $s->id])" title="فواتير المورد" icon="fe-file-text" class="btn-icon-print" />
                            @if(can('suppliers.update'))<x-edit-link :href="route('suppliers.edit', $s)" />@endif
                            @if(can('suppliers.delete'))<x-delete-form :action="route('suppliers.destroy', $s)" message="هل أنت متأكد من حذف هذا المورد؟ سيتم إزالة بياناته من النظام." title="حذف المورد" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="10" class="cell-empty text-center text-muted"><i class="fe fe-briefcase"></i>لا يوجد موردون</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$suppliers" />
</div>

<div class="modal fade" id="addSupplierModal" tabindex="-1" role="dialog" aria-labelledby="addSupplierModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('suppliers.store') }}">
                @csrf
                <input type="hidden" name="_form" value="supplier">
                <div class="modal-header">
                    <h5 class="modal-title" id="addSupplierModalTitle">إضافة مورد</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="row">
                        <div class="col-md-4 form-group"><label>الكود</label><input name="code" class="form-control" value="{{ old('code') }}"></div>
                        <div class="col-md-4 form-group"><label>الاسم *</label><input name="name" class="form-control @error('name') is-invalid @enderror" required value="{{ old('name') }}">@error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror</div>
                        <div class="col-md-4 form-group"><label>الهاتف</label><input name="phone" class="form-control" value="{{ old('phone') }}"></div>
                        <div class="col-md-4 form-group"><label>البريد</label><input name="email" type="email" class="form-control" value="{{ old('email') }}"></div>
                        <div class="col-md-4 form-group"><label>الرقم الضريبي</label><input name="tax_number" class="form-control" value="{{ old('tax_number') }}"></div>
                        <div class="col-md-4 form-group"><label>الحالة</label>
                            <select name="status" class="form-control">
                                <option value="active" @selected(old('status', 'active')==='active')>نشط</option>
                                <option value="inactive" @selected(old('status')==='inactive')>غير نشط</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group"><label>رصيد أول المدة</label><input name="opening_balance" type="number" step="0.01" class="form-control" value="{{ old('opening_balance') }}"></div>
                        <div class="col-md-12 form-group"><label>العنوان</label><input name="address" class="form-control" value="{{ old('address') }}"></div>
                        <div class="col-md-12 form-group"><label>ملاحظات</label><textarea name="notes" class="form-control">{{ old('notes') }}</textarea></div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-primary">حفظ</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('js')
@if($openAddModal)
<script>
$(function () { $('#addSupplierModal').modal('show'); });
</script>
@endif
@endsection
