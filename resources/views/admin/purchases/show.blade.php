@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'فاتورة '.$invoice->number,
        'subtitle' => 'فواتير المشتريات',
        'action' => '<a href="'.route('purchases.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل الفواتير</a>',
        'exports' => [
            ['sheet' => 'purchase-invoice-items', 'params' => ['purchase' => $invoice->id]],
        ],
    ])
@endsection
@section('content')
<x-invoice-sheet
    :invoice="$invoice"
    :party="$invoice->supplier"
    party-label="المورد"
    code-label="كود المورد"
>
    <x-slot:actions>
        @if(in_array($invoice->status, ['confirmed','partial']) && can('purchases.update'))
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#payModal">صرف للمورد</button>
        @endif
        @if($invoice->status === 'draft' && can('purchases.update'))
            <form method="post" action="{{ route('purchases.confirm', $invoice) }}">@csrf<button class="btn btn-success">تأكيد</button></form>
            <a class="btn btn-light" href="{{ route('purchases.edit', $invoice) }}">تعديل</a>
            <form method="post" action="{{ route('purchases.cancel', $invoice) }}" data-confirm="هل أنت متأكد من إلغاء هذه الفاتورة؟" data-confirm-title="إلغاء الفاتورة" data-confirm-ok="نعم، إلغاء" data-confirm-icon="fe fe-x-circle">@csrf<button class="btn btn-danger">إلغاء</button></form>
        @endif
        @if(can('purchases.delete'))
            <form method="post" action="{{ route('purchases.destroy', $invoice) }}" data-confirm="هل أنت متأكد أنك تريد استكمال عملية الحذف؟" data-confirm-title="هل تريد الاستمرار؟" data-confirm-ok="تأكيد">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fe fe-trash-2"></i><span>حذف</span></button>
            </form>
        @endif
        <button type="button" class="btn btn-primary inv-print" data-print-url="{{ route('purchases.print', $invoice) }}"><i class="fe fe-printer"></i><span>طباعة</span></button>
    </x-slot:actions>
</x-invoice-sheet>

<div class="card mt-3">
    <div class="card-body">
        <h5 class="mb-3">مرفق الفاتورة</h5>
        @if($invoice->attachmentUrl())
            @if($invoice->attachmentIsImage())
                <button type="button" class="btn p-0 border-0 bg-transparent" data-toggle="modal" data-target="#attachmentModal" aria-label="عرض مرفق {{ $invoice->number }}">
                    <img src="{{ $invoice->attachmentUrl() }}" alt="مرفق {{ $invoice->number }}" style="max-height:180px;border-radius:8px;border:1px solid #E8EDF5">
                </button>
            @else
                <a class="btn btn-outline-primary" href="{{ $invoice->attachmentUrl() }}" target="_blank" rel="noopener">
                    <i class="fe fe-file ml-1"></i> {{ $invoice->number }}
                </a>
            @endif
        @else
            <p class="text-muted mb-3">لا يوجد مرفق لهذه الفاتورة.</p>
        @endif
        @if(can('purchases.update'))
            <form method="post" action="{{ route('purchases.attachment', $invoice) }}" enctype="multipart/form-data" class="mt-3">
                @csrf
                <div class="form-group mb-2">
                    <label for="show_purchase_attachment">{{ $invoice->attachmentUrl() ? 'استبدال المرفق' : 'رفع مرفق' }}</label>
                    <input id="show_purchase_attachment" type="file" name="attachment" class="form-control @error('attachment') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png,.webp">
                    <small class="text-muted d-block mt-1">يتحفظ باسم {{ $invoice->number }}.</small>
                    @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                @if($invoice->attachmentUrl())
                    <label class="d-block mb-2">
                        <input type="checkbox" name="remove_attachment" value="1" @checked(old('remove_attachment'))>
                        حذف المرفق
                    </label>
                @endif
                <button class="btn btn-primary" type="submit">حفظ المرفق</button>
            </form>
        @endif
    </div>
</div>

@if($invoice->attachmentUrl() && $invoice->attachmentIsImage())
<div class="modal fade" id="attachmentModal" tabindex="-1" role="dialog" aria-labelledby="attachmentModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="attachmentModalTitle">مرفق {{ $invoice->number }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body text-center p-3">
                <img src="{{ $invoice->attachmentUrl() }}" alt="مرفق {{ $invoice->number }}" style="max-width:100%;max-height:75vh;border-radius:8px">
            </div>
        </div>
    </div>
</div>
@endif

@if(in_array($invoice->status, ['confirmed','partial']) && can('purchases.update'))
<div class="modal fade" id="payModal" tabindex="-1" role="dialog" aria-labelledby="payModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('purchases.pay', $invoice) }}">
                @csrf
                <input type="hidden" name="_form" value="pay">
                <div class="modal-header">
                    <h5 class="modal-title" id="payModalTitle">صرف فاتورة {{ $invoice->number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="pay-modal-summary">
                        <div><span>الإجمالي</span><strong>{{ money($invoice->total) }}</strong></div>
                        <div><span>المدفوع</span><strong class="text-success">{{ money($invoice->paid) }}</strong></div>
                        <div><span>المتبقي</span><strong class="text-danger">{{ money($invoice->remaining) }}</strong></div>
                    </div>
                    <div class="form-group">
                        <label for="pay_treasury">الخزينة</label>
                        <select id="pay_treasury" name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                            @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected(old('treasury_id') == $t->id)>{{ $t->name }}</option>@endforeach
                        </select>
                        @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-md-0">
                            <label for="pay_amount">المبلغ</label>
                            <input id="pay_amount" type="number" step="0.01" min="0.01" name="amount" max="{{ $invoice->remaining }}" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $invoice->remaining) }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label for="pay_date">التاريخ</label>
                            <input id="pay_date" type="date" name="transacted_at" class="form-control" value="{{ old('transacted_at', now()->toDateString()) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-danger"><i class="fe fe-upload ml-1"></i> تسجيل الصرف</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
@section('js')
@if($errors->any() && old('_form') === 'pay')
<script>
$(function () { $('#payModal').modal('show'); });
</script>
@endif
@endsection
