@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'العملاء',
        'action' => can('customers.create') ? '<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addCustomerModal"><i class="fe fe-plus ml-1"></i> إضافة عميل جديد</button>' : '',
        'export' => 'customers',
    ])
@endsection
@section('content')
@php $openAddModal = old('_form') === 'customer'; @endphp

<x-filter-drawer>
    <x-filter-field label="الكود" name="code" placeholder="أدخل الكود..." />
    <x-filter-field label="اسم العميل" name="name" type="select" :options="$filters['names']" />
    <x-filter-field label="الموبايل" name="phone" placeholder="أدخل الموبايل..." />
    <x-filter-field label="العنوان" name="address" placeholder="أدخل العنوان..." />
    <x-filter-field label="الرصيد" name="balance" type="number" placeholder="أدخل الرصيد..." />
    <x-filter-field label="أضيف بواسطة" name="created_by" type="select" :options="$filters['creators']" />
    <x-filter-field label="تاريخ الإضافة" name="created_on" type="date" />
</x-filter-drawer>

<div class="card">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-users"></i> العملاء</h4>
        <span class="count-badge">{{ $customers->total() }} عميل</span>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered table-hover mb-0">
            <thead>
                <tr>
                    <th class="col-serial">#</th>
                    <th>الكود</th>
                    <th>اسم العميل</th>
                    <th>الموبايل</th>
                    <th>العنوان</th>
                    <th>الرصيد</th>
                    <th>أضيف بواسطة</th>
                    <th>تاريخ الإضافة</th>
                    <th class="col-actions">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
            @forelse($customers as $c)
                <tr>
                    <td class="col-serial">{{ row_no($customers, $loop) }}</td>
                    <td class="text-muted">{{ $c->code }}</td>
                    <td class="cell-strong"><a href="{{ route('customers.show', $c) }}">{{ $c->name }}</a></td>
                    <td dir="ltr">{{ $c->phone ?: '-' }}</td>
                    <td>{{ $c->address ?: '-' }}</td>
                    <td class="cell-money {{ $c->current_balance > 0 ? 'is-neg' : '' }}">{{ money($c->current_balance) }}</td>
                    <td>{{ $c->creator->name ?? 'غير محدد' }}</td>
                    <td class="text-muted">{{ optional($c->created_at)->format('Y-m-d') }}</td>
                    <td class="col-actions">
                        <div class="btn-actions">
                            <x-view-link :href="route('customers.show', $c)" title="كشف الحساب" />
                            <x-view-link :href="route('sales.index', ['customer_id' => $c->id])" title="فواتير العميل" icon="fe-file-text" class="btn-icon-print" />
                            @if(can('customers.update'))<x-edit-link :href="route('customers.edit', $c)" />@endif
                            @if(can('customers.delete'))<x-delete-form :action="route('customers.destroy', $c)" message="هل أنت متأكد من حذف هذا العميل؟ سيتم إزالة بياناته من النظام." title="حذف العميل" />@endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="9" class="cell-empty text-center text-muted"><i class="fe fe-users"></i>لا يوجد عملاء</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <x-table-pager :items="$customers" />
</div>

<div class="modal fade" id="addCustomerModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('customers.store') }}">
                @csrf
                <input type="hidden" name="_form" value="customer">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCustomerModalTitle">إضافة عميل</h5>
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
                        <div class="col-md-6 form-group"><label>حد الائتمان</label><input name="credit_limit" type="number" step="0.01" class="form-control" value="{{ old('credit_limit') }}"></div>
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
$(function () { $('#addCustomerModal').modal('show'); });
</script>
@endif
@endsection
