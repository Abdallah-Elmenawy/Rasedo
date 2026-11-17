@props([
    'invoice',
    'party' => null,
    'partyLabel' => 'فاتورة إلى',
    'codeLabel' => 'كود العميل',
])

@once
    <link rel="stylesheet" href="{{ asset('assets/css/invoice-sheet.css') }}?v=4">
@endonce

@php
    $company = company();
    $isPurchase = $invoice instanceof \App\Models\Purchases\PurchaseInvoice;
    $docTitle = $isPurchase ? 'فاتورة مشتريات' : 'فاتورة مبيعات';
    $subtotal = (float) $invoice->subtotal;
    $tax = (float) $invoice->tax;
    $taxRate = $subtotal > 0 ? round(($tax / $subtotal) * 100, 2) : 0;
    $taxRateLabel = rtrim(rtrim(number_format($taxRate, 2, '.', ''), '0'), '.');
    $fmtDate = fn ($date) => $date ? $date->locale('ar')->translatedFormat('j F Y') : '—';
    $fmtQty = function ($qty) {
        $text = rtrim(rtrim(number_format((float) $qty, 3, '.', ''), '0'), '.');

        return $text === '' ? '0' : $text;
    };
    $currency = $company?->currency_symbol ?: ($company?->currency ?: 'ج.م');
@endphp

<div {{ $attributes->merge(['class' => 'inv-sheet']) }}>
    <div class="inv-intro">
        <div class="inv-company">
            @if(company_shows_name())
                <strong>{{ $company->name ?? 'Rasedo' }}</strong>
            @endif
            @if($company?->legal_name && $company->legal_name !== $company->name)
                <span>{{ $company->legal_name }}</span>
            @endif
            @if($company?->tax_number)
                <span>الرقم الضريبي: {{ $company->tax_number }}</span>
            @endif
            @if($company?->phone)
                <span>الهاتف: {{ $company->phone }}</span>
            @endif
            @if($company?->address)
                <span>{{ $company->address }}</span>
            @endif
            @if($company?->email)
                <span>{{ $company->email }}</span>
            @endif
        </div>
        @if(company_shows_logo())
            <div class="inv-logo-card">
                <img class="inv-logo" src="{{ company_logo_url() }}" alt="{{ $company->name ?? 'Rasedo' }}">
            </div>
        @endif
        <div class="inv-billto">
            <span class="inv-billto-label">{{ $partyLabel }}</span>
            <strong>{{ $party->name ?? '—' }}</strong>
            @if($party?->phone)
                <span>هاتف: {{ $party->phone }}</span>
            @endif
            @if($party?->address)
                <span>{{ $party->address }}</span>
            @endif
            @if($party?->email)
                <span>{{ $party->email }}</span>
            @endif
        </div>
    </div>

    <div class="inv-mid">
        <div class="inv-doc">
            <span class="inv-title">{{ $docTitle }}</span>
            <span class="inv-status inv-status-{{ $invoice->status }}">{{ invoice_status_label($invoice->status) }}</span>
        </div>
        <div class="inv-facts">
            <div class="inv-fact">
                <span>رقم الفاتورة</span>
                <strong>{{ $invoice->number }}</strong>
            </div>
            <div class="inv-fact">
                <span>{{ $codeLabel }}</span>
                <strong>{{ $party->code ?? '—' }}</strong>
            </div>
            <div class="inv-fact">
                <span>تاريخ الإصدار</span>
                <strong>{{ $fmtDate($invoice->invoice_date) }}</strong>
            </div>
            <div class="inv-fact">
                <span>تاريخ الاستحقاق</span>
                <strong>{{ $fmtDate($invoice->due_date) }}</strong>
            </div>
            <div class="inv-fact">
                <span>العملة</span>
                <strong class="inv-money">{{ $currency }}</strong>
            </div>
        </div>
    </div>

    <div class="inv-table-wrap">
        <table class="inv-table">
            <thead>
                <tr>
                    <th class="is-serial">#</th>
                    <th class="is-type">النوع</th>
                    <th class="is-desc">الوصف</th>
                    <th class="is-num">الكمية</th>
                    <th class="is-num">سعر الوحدة</th>
                    <th class="is-num">المبلغ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoice->items as $item)
                    @php
                        $description = trim((string) ($item->description ?: $item->product?->description ?: ''));
                    @endphp
                    <tr>
                        <td class="is-serial">{{ $loop->iteration }}</td>
                        <td class="is-type">{{ $item->product->name ?? '—' }}</td>
                        <td class="is-desc">{{ $description !== '' ? $description : '—' }}</td>
                        <td class="is-num">{{ $fmtQty($item->qty) }}</td>
                        <td class="is-num"><span class="inv-money">{{ money($item->unit_price) }}</span></td>
                        <td class="is-num is-amount"><span class="inv-money">{{ money($item->total) }}</span></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="inv-empty">لا توجد بنود</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="inv-bottom">
        <div class="inv-totals">
            <div class="inv-total-row">
                <span>المجموع الفرعي</span>
                <strong class="inv-money">{{ money($invoice->subtotal) }}</strong>
            </div>
            <div class="inv-total-row">
                <span>الضريبة (<bdi>{{ $taxRateLabel }}%</bdi>)</span>
                <strong class="inv-money">{{ money($invoice->tax) }}</strong>
            </div>
            <div class="inv-total-row">
                <span>الخصم</span>
                <strong class="inv-money">{{ money($invoice->discount) }}</strong>
            </div>
            <div class="inv-total-row">
                <span>المدفوع</span>
                <strong class="inv-money">{{ money($invoice->paid) }}</strong>
            </div>
            <div class="inv-total-row">
                <span>المتبقي</span>
                <strong class="inv-money">{{ money($invoice->remaining) }}</strong>
            </div>
            <div class="inv-total-row is-due">
                <span>الإجمالي</span>
                <strong class="inv-money">{{ money($invoice->total) }}</strong>
            </div>
        </div>
    </div>

    @isset($actions)
        <div class="inv-actions no-print">
            {{ $actions }}
        </div>
    @endisset
</div>

@once
    <script>
        (function () {
            var allowed = {
                DIV: 1, SPAN: 1, STRONG: 1, TABLE: 1, THEAD: 1, TBODY: 1, TFOOT: 1,
                TR: 1, TH: 1, TD: 1, P: 1, BDI: 1, BUTTON: 1, I: 1, FORM: 1, BR: 1,
                HEADER: 1, SECTION: 1, IMG: 1
            };

            function hideForeign(el) {
                el.classList.add('print-ext-hide');
                el.setAttribute('hidden', 'hidden');
            }

            function preparePrint() {
                var sheet = document.querySelector('.inv-sheet');
                if (!sheet) return;
                document.querySelectorAll('body *').forEach(function (el) {
                    if (el === sheet || sheet.contains(el) || el.contains(sheet)) return;
                    hideForeign(el);
                });
                sheet.querySelectorAll('*').forEach(function (el) {
                    var tag = el.tagName;
                    var style = window.getComputedStyle(el);
                    var foreignTag = !allowed[tag] || tag.indexOf('-') !== -1;
                    var fixed = style.position === 'fixed' || style.position === 'sticky';
                    var cls = typeof el.className === 'string' ? el.className : '';
                    if (el.classList && el.classList.contains('inv-logo')) return;
                    var inParty = el.closest('.inv-company, .inv-billto');
                    var foreignParty = inParty && (
                        (cls && cls.indexOf('inv-') === -1) ||
                        style.backgroundImage !== 'none' ||
                        (!el.textContent.trim() && !(el.querySelector && el.querySelector('.inv-logo')))
                    );
                    if (foreignTag || fixed || el.shadowRoot || foreignParty) hideForeign(el);
                });
            }

            window.addEventListener('beforeprint', preparePrint);
        })();
    </script>
@endonce
