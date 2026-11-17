@php
    $requested = request('format');
    $formatKey = \App\Support\InvoicePrintFormat::key($requested !== null && $requested !== '' ? $requested : optional(company())->print_format);
    $format = \App\Support\InvoicePrintFormat::get($formatKey);
@endphp
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $invoice->number }}</title>
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/css/invoice-print-formats.css') }}?v=3" rel="stylesheet">
</head>
<body class="print-{{ $formatKey }}">
@if($format['kind'] === 'thermal')
    @include('invoices.print.thermal-'.$format['size'])
    <div class="print-toolbar no-print">
        <button type="button" class="btn btn-primary" onclick="window.print()">طباعة</button>
        <a class="btn btn-light" href="{{ $backUrl }}">رجوع</a>
    </div>
@else
    <div class="inv-wrap">
        <x-invoice-sheet
            :invoice="$invoice"
            :party="$party"
            :party-label="$partyLabel"
            :code-label="$codeLabel"
        >
            <x-slot:actions>
                <button type="button" class="btn btn-primary inv-print" onclick="window.print()"><i class="fe fe-printer"></i><span>طباعة</span></button>
                <a class="btn btn-light" href="{{ $backUrl }}">رجوع للفاتورة</a>
            </x-slot:actions>
        </x-invoice-sheet>
    </div>
@endif
<script>
    (function () {
        var printed = false;
        function openDialog() {
            if (printed) return;
            printed = true;
            window.print();
        }
        if (document.fonts && document.fonts.ready) {
            document.fonts.ready.then(openDialog);
        }
        setTimeout(openDialog, 800);
    })();
</script>
</body>
</html>
