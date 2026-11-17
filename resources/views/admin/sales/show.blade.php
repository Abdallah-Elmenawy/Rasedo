@extends('layouts.master')
@section('page-header')
    @include('admin.partials.page-header', [
        'title' => 'فاتورة '.$invoice->number,
        'subtitle' => 'فواتير المبيعات',
        'action' => '<a href="'.route('sales.index').'" class="btn btn-light"><i class="fe fe-arrow-right ml-1"></i> كل الفواتير</a>',
        'exports' => [
            ['sheet' => 'sales-invoice-items', 'params' => ['sale' => $invoice->id]],
        ],
    ])
@endsection
@section('content')
<x-invoice-sheet
    :invoice="$invoice"
    :party="$invoice->customer"
    party-label="فاتورة إلى"
    code-label="كود العميل"
>
    <x-slot:actions>
        @if(in_array($invoice->status, ['confirmed','partial']) && can('sales.update'))
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#collectModal">تحصيل</button>
        @endif
        @if($invoice->status === 'draft' && can('sales.update'))
            <form method="post" action="{{ route('sales.confirm', $invoice) }}">@csrf<button class="btn btn-success">تأكيد</button></form>
            <a class="btn btn-light" href="{{ route('sales.edit', $invoice) }}">تعديل</a>
            <form method="post" action="{{ route('sales.cancel', $invoice) }}" data-confirm="هل أنت متأكد من إلغاء هذه الفاتورة؟" data-confirm-title="إلغاء الفاتورة" data-confirm-ok="نعم، إلغاء" data-confirm-icon="fe fe-x-circle">@csrf<button class="btn btn-danger">إلغاء</button></form>
        @endif
        @if(can('sales.delete'))
            <form method="post" action="{{ route('sales.destroy', $invoice) }}" data-confirm="هل أنت متأكد أنك تريد استكمال عملية الحذف؟" data-confirm-title="هل تريد الاستمرار؟" data-confirm-ok="تأكيد">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger"><i class="fe fe-trash-2"></i><span>حذف</span></button>
            </form>
        @endif
        <button type="button" class="btn btn-primary inv-print" data-print-url="{{ route('sales.print', $invoice) }}"><i class="fe fe-printer"></i><span>طباعة</span></button>
    </x-slot:actions>
</x-invoice-sheet>

@if(in_array($invoice->status, ['confirmed','partial']) && can('sales.update'))
<div class="modal fade" id="collectModal" tabindex="-1" role="dialog" aria-labelledby="collectModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <form method="post" action="{{ route('sales.pay', $invoice) }}">
                @csrf
                <input type="hidden" name="_form" value="collect">
                <div class="modal-header">
                    <h5 class="modal-title" id="collectModalTitle">تحصيل فاتورة {{ $invoice->number }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body px-4 pt-4">
                    <div class="pay-modal-summary">
                        <div><span>الإجمالي</span><strong>{{ money($invoice->total) }}</strong></div>
                        <div><span>المدفوع</span><strong class="text-success">{{ money($invoice->paid) }}</strong></div>
                        <div><span>المتبقي</span><strong class="text-danger">{{ money($invoice->remaining) }}</strong></div>
                    </div>
                    <div class="form-group">
                        <label for="collect_treasury">الخزينة</label>
                        <select id="collect_treasury" name="treasury_id" class="form-control @error('treasury_id') is-invalid @enderror" required>
                            @foreach($treasuries as $t)<option value="{{ $t->id }}" @selected(old('treasury_id') == $t->id)>{{ $t->name }}</option>@endforeach
                        </select>
                        @error('treasury_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 form-group mb-md-0">
                            <label for="collect_amount">المبلغ</label>
                            <input id="collect_amount" type="number" step="0.01" min="0.01" name="amount" max="{{ $invoice->remaining }}" class="form-control @error('amount') is-invalid @enderror" value="{{ old('amount', $invoice->remaining) }}" required>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 form-group mb-0">
                            <label for="collect_date">التاريخ</label>
                            <input id="collect_date" type="date" name="transacted_at" class="form-control" value="{{ old('transacted_at', now()->toDateString()) }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4">
                    <button type="submit" class="btn btn-success"><i class="fe fe-download ml-1"></i> تسجيل التحصيل</button>
                    <button type="button" class="btn btn-light" data-dismiss="modal">إلغاء</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
@section('js')
@if($errors->any() && old('_form') === 'collect')
<script>
$(function () { $('#collectModal').modal('show'); });
</script>
@endif
@endsection
