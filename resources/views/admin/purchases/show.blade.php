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
@section('css')
<style>
    .inv-attachment .inv-attachment-grid {
        display: grid;
        grid-template-columns: 220px minmax(0, 1fr);
        gap: 24px;
        align-items: start;
    }
    .inv-attachment .inv-attachment-preview { width: 100%; }
    .inv-attachment .inv-attachment-thumb,
    .inv-attachment .inv-attachment-file,
    .inv-attachment .inv-attachment-empty {
        position: relative;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        width: 100%;
        aspect-ratio: 1 / 1;
        border-radius: 10px;
        border: 1px solid #E6EEF8;
        background: #F7FAFE;
        overflow: hidden;
        text-decoration: none !important;
    }
    .inv-attachment .inv-attachment-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform .25s ease;
    }
    .inv-attachment .inv-attachment-thumb:hover img { transform: scale(1.04); }
    .inv-attachment .inv-attachment-zoom {
        position: absolute;
        bottom: 8px;
        right: 8px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(30, 78, 155, .88);
        color: #fff;
        font-size: 12px;
        font-weight: 800;
    }
    .inv-attachment .inv-attachment-file { color: #1E4E9B; }
    .inv-attachment .inv-attachment-file i { font-size: 40px; }
    .inv-attachment .inv-attachment-file span { font-weight: 800; }
    .inv-attachment .inv-attachment-file small { color: #6B7280; font-weight: 700; }
    .inv-attachment .inv-attachment-empty { color: #9CA3AF; font-weight: 700; font-size: 13px; text-align: center; padding: 12px; }
    .inv-attachment .inv-attachment-empty i { font-size: 34px; color: #C7D3E3; }
    .inv-attachment .inv-attachment-form,
    .inv-attachment .inv-attachment-form * { text-align: right; }
    .inv-attachment .inv-attachment-form { display: flex; flex-direction: column; gap: 8px; }
    .inv-attachment .inv-attachment-label { margin: 0; font-weight: 800; color: #2D3E50; }
    .inv-attachment .inv-attachment-actions {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-top: 6px;
    }
    .inv-attachment .inv-attachment-remove {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin: 0;
        color: #EF4444;
        font-weight: 700;
        cursor: pointer;
    }
    @media (max-width: 575.98px) {
        .inv-attachment .inv-attachment-grid { grid-template-columns: 1fr; }
        .inv-attachment .inv-attachment-preview { max-width: 240px; margin: 0 auto; }
    }
</style>
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

<div class="card mt-3 inv-attachment">
    <div class="card-header">
        <h4 class="card-title"><i class="fe fe-paperclip"></i> مرفق الفاتورة</h4>
    </div>
    <div class="card-body">
        <div class="inv-attachment-grid">
            <div class="inv-attachment-preview">
                @if($invoice->attachmentUrl())
                    @if($invoice->attachmentIsImage())
                        <a href="#attachmentModal" class="inv-attachment-thumb" data-toggle="modal" aria-label="عرض مرفق {{ $invoice->number }}">
                            <img src="{{ $invoice->attachmentUrl() }}" alt="مرفق {{ $invoice->number }}">
                            <span class="inv-attachment-zoom"><i class="fe fe-maximize-2"></i> عرض</span>
                        </a>
                    @else
                        <a class="inv-attachment-file" href="{{ $invoice->attachmentUrl() }}" target="_blank" rel="noopener">
                            <i class="fe fe-file-text"></i>
                            <span>{{ $invoice->number }}</span>
                            <small>فتح الملف</small>
                        </a>
                    @endif
                @else
                    <div class="inv-attachment-empty">
                        <i class="fe fe-image"></i>
                        <span>لا يوجد مرفق لهذه الفاتورة</span>
                    </div>
                @endif
            </div>

            @if(can('purchases.update'))
                <form method="post" action="{{ route('purchases.attachment', $invoice) }}" enctype="multipart/form-data" class="inv-attachment-form">
                    @csrf
                    <label class="inv-attachment-label" for="show_purchase_attachment">{{ $invoice->attachmentUrl() ? 'استبدال المرفق' : 'رفع مرفق' }}</label>
                    <x-file-input name="attachment" id="show_purchase_attachment" accept=".pdf,.jpg,.jpeg,.png,.webp" placeholder="اختر ملفاً (PDF أو صورة)" />
                    <small class="text-muted d-block">يتحفظ باسم {{ $invoice->number }}.</small>
                    @error('attachment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    <div class="inv-attachment-actions">
                        @if($invoice->attachmentUrl())
                            <label class="inv-attachment-remove">
                                <input type="checkbox" name="remove_attachment" value="1" @checked(old('remove_attachment'))>
                                <span>حذف المرفق</span>
                            </label>
                        @endif
                        <button class="btn btn-primary" type="submit"><i class="fe fe-save ml-1"></i> حفظ المرفق</button>
                    </div>
                </form>
            @endif
        </div>
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
