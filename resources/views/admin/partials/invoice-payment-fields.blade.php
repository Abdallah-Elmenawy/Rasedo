@php
    $payStatus = old('payment_status', 'paid');
    $treasuries = $treasuries ?? collect();
@endphp
<div class="row invoice-pay-fields">
    <div class="col-md-3 form-group">
        <label>إجمالي الفاتورة</label>
        <input type="text" class="form-control js-invoice-total" value="0.00" readonly tabindex="-1">
    </div>
    <div class="col-md-3 form-group">
        <label>حالة الفاتورة</label>
        <select name="payment_status" class="form-control js-payment-status">
            <option value="paid" @selected($payStatus === 'paid')>مدفوع</option>
            <option value="unpaid" @selected($payStatus === 'unpaid')>غير مدفوع</option>
        </select>
    </div>
    <div class="col-md-3 form-group js-paid-wrap" @if($payStatus === 'unpaid') hidden @endif>
        <label>{{ $paidLabel }}</label>
        <input name="paid_amount" type="number" min="0" step="0.01" class="form-control" value="{{ old('paid_amount', 0) }}" @if(old('paid_amount') !== null) data-touched="1" @endif>
    </div>
    <div class="col-md-3 form-group js-treasury-wrap" @if($payStatus === 'unpaid') hidden @endif>
        <label>الخزينة</label>
        <select name="treasury_id" class="form-control">
            @foreach($treasuries as $treasury)
                <option value="{{ $treasury->id }}" @selected(old('treasury_id', $treasuries->first()?->id) == $treasury->id)>{{ $treasury->name }}</option>
            @endforeach
        </select>
        @if($treasuries->isEmpty())
            <small class="text-danger">لا توجد خزينة نشطة للتحصيل أو الصرف.</small>
        @endif
    </div>
</div>
@once
<script>
function invoiceFormTotal(form) {
    let total = 0;
    form.querySelectorAll('table tbody tr').forEach(function (tr) {
        const qty = Number((tr.querySelector('[name*="[qty]"]') || {}).value || 0);
        const price = Number((tr.querySelector('.price') || {}).value || 0);
        const tax = Number((tr.querySelector('[name*="[tax]"]') || {}).value || 0);
        const discounts = tr.querySelectorAll('[name*="[discount]"]');
        const disc = Number((discounts[0] || {}).value || 0);
        total += (qty * price) - disc + tax;
    });
    const headerDisc = Number((form.querySelector('input[name="discount"]') || {}).value || 0);
    return Math.max(total - headerDisc, 0);
}
function syncInvoiceMoney(form) {
    const total = invoiceFormTotal(form);
    const totalInput = form.querySelector('.js-invoice-total');
    if (totalInput) totalInput.value = total.toFixed(2);
    const status = form.querySelector('.js-payment-status');
    const amount = form.querySelector('[name="paid_amount"]');
    if (!status || !amount || status.value !== 'paid') return;
    if (amount.dataset.touched === '1') return;
    amount.value = total.toFixed(2);
}
function invoiceMoneyForm(target) {
    const form = target && target.closest ? target.closest('form') : null;
    return form && form.querySelector('.js-invoice-total') ? form : null;
}
document.addEventListener('input', function (e) {
    const form = invoiceMoneyForm(e.target);
    if (!form) return;
    if (e.target.name === 'paid_amount') {
        e.target.dataset.touched = e.target.value === '' ? '' : '1';
        return;
    }
    syncInvoiceMoney(form);
});
document.addEventListener('change', function (e) {
    if (e.target.classList.contains('js-payment-status')) {
        const form = e.target.closest('form');
        if (!form) return;
        const paid = e.target.value === 'paid';
        form.querySelectorAll('.js-paid-wrap, .js-treasury-wrap').forEach(function (el) {
            el.hidden = !paid;
        });
        const amount = form.querySelector('[name="paid_amount"]');
        if (!paid && amount) {
            amount.value = 0;
            amount.dataset.touched = '';
        }
        if (paid) syncInvoiceMoney(form);
        return;
    }
    const form = invoiceMoneyForm(e.target);
    if (form) setTimeout(function () { syncInvoiceMoney(form); }, 0);
});
document.addEventListener('click', function (e) {
    const btn = e.target.closest('button[name="confirm"][value="1"], .js-add-item, .del, .line-serial-x');
    if (!btn) return;
    const form = btn.closest('form');
    if (!form || !form.querySelector('.js-invoice-total')) return;
    setTimeout(function () { syncInvoiceMoney(form); }, 0);
});
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form').forEach(function (form) {
        if (form.querySelector('.js-invoice-total')) syncInvoiceMoney(form);
    });
});
</script>
@endonce
