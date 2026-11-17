@php
    $company = company();
    $isPurchase = $invoice instanceof \App\Models\Purchases\PurchaseInvoice;
    $paper = $paper ?? '80';
    $docTitle = $isPurchase ? 'المشتريات' : \App\Support\ReceiptText::salesTitle();
    $partyName = $party->name ?? '—';
    $cashier = $invoice->creator?->name;
    $payment = $invoice->payments->first();
    $methods = [
        'cash' => 'مسدد نقدا',
        'visa' => 'فيزا',
        'mada' => 'مدى',
        'wallet' => 'محفظة',
        'other' => 'أخرى',
    ];
    $payLabel = $methods[$payment->method ?? ''] ?? (((float) $invoice->paid <= 0 && (float) $invoice->remaining > 0) ? 'آجل' : 'مسدد نقدا');
    $orderType = $isPurchase ? null : \App\Support\ReceiptText::orderLabel($invoice->order_type ?? null);
    $extraNote = $invoice->notes;
    if (is_string($extraNote) && preg_match('/نقطة بيع\s*[—\-]\s*(.+)/u', $extraNote, $match)) {
        $orderType = $orderType ?: trim($match[1]);
        $extraNote = null;
    }
    $qtyTotal = $invoice->items->sum(fn ($item) => (float) $item->qty);
    $amount = fn ($value) => number_format((float) $value, 2, '.', '');
    $printedAt = $invoice->created_at ?? $invoice->invoice_date;
@endphp
<article class="ticket thermal-{{ $paper }}">
    <div class="ticket-brand {{ company_logo_on_right() ? 'is-logo-right' : 'is-logo-left' }}">
        @if(company_shows_logo())
            <img class="ticket-logo" src="{{ company_logo_url() }}" alt="{{ $company->name ?? 'Rasedo' }}">
        @endif
        @if(company_shows_name())
            <h1 class="ticket-shop">{{ $company->name ?? 'Rasedo' }}</h1>
        @endif
    </div>
    <p class="ticket-kind">{{ $docTitle }}</p>
    @if($orderType)
        <p class="ticket-type">{{ $orderType }}</p>
    @endif

    <div class="ticket-no">{{ $invoice->number }}</div>
    <div class="ticket-meta">
        <div class="ticket-who">
            <div>رقم الفاتورة</div>
            @if($cashier)
                <div>كاشير : {{ $cashier }}</div>
            @endif
            <div>{{ $isPurchase ? 'المورد' : 'العميل' }} : {{ $partyName }}</div>
        </div>
        <div class="ticket-date">{{ optional($invoice->invoice_date)->format('Y-m-d') }}</div>
    </div>

    <table class="ticket-table">
        <thead>
            <tr>
                <th>اسم الصنف</th>
                <th>الكمية</th>
                <th>السعر</th>
                <th>الاجمالي</th>
            </tr>
        </thead>
        <tbody>
        @foreach($invoice->items as $item)
            <tr>
                <td class="name">{{ $item->product->name ?? '—' }}</td>
                <td>{{ qty($item->qty) }}</td>
                <td>{{ $amount($item->unit_price) }}</td>
                <td>{{ $amount($item->total) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    <div class="ticket-sum">
        <span>الكميات {{ qty($qtyTotal) }}</span>
        <span>الاجمالي {{ $amount($invoice->subtotal) }}</span>
    </div>
    @if((float) $invoice->discount > 0)
        <div class="ticket-line"><span>الخصم</span><span>{{ $amount($invoice->discount) }}</span></div>
    @endif
    @if((float) $invoice->tax > 0)
        <div class="ticket-line"><span>الضريبة</span><span>{{ $amount($invoice->tax) }}</span></div>
    @endif
    <div class="ticket-net">
        <span>الصافي</span>
        <span>{{ $amount($invoice->total) }}</span>
    </div>

    <div class="ticket-pay">
        <div><span>{{ $payLabel }}</span><span>{{ $amount($payment->amount ?? $invoice->paid) }}</span></div>
        <div><span>المدفوع</span><span>{{ $amount($invoice->paid) }}</span></div>
        <div><span>الباقي</span><span>{{ $amount($invoice->remaining) }}</span></div>
    </div>

    @if($extraNote)
        <p class="ticket-address">{{ $extraNote }}</p>
    @endif
    @if($company?->address)
        <p class="ticket-address">{{ $company->address }}</p>
    @endif
    @if($company?->phone)
        <p class="ticket-phone">{{ $company->phone }}</p>
    @endif

    <div class="ticket-foot">
        <span>{{ optional($printedAt)->format('d/m/Y') }}</span>
        <span dir="ltr">{{ optional($invoice->created_at)->format('h:i:s a') }}</span>
    </div>
</article>
