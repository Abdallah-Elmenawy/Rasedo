<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title> </title>
    <link href="{{ asset('assets/css/icons.css') }}" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        html, body { margin: 0; background: #F4F7FE; }
        body { padding: 28px 16px 40px; }
        .inv-wrap { max-width: 980px; margin: 0 auto; }
        @media print {
            body { padding: 0; background: #fff; }
        }
    </style>
</head>
<body>
<div class="inv-wrap">
    <x-invoice-sheet
        :invoice="$invoice"
        :party="$invoice->customer"
        party-label="فاتورة إلى"
        code-label="كود العميل"
    >
        <x-slot:actions>
            <button type="button" class="btn btn-primary inv-print" onclick="window.print()"><i class="fe fe-printer"></i><span>طباعة</span></button>
            <a class="btn btn-light" href="{{ route('sales.show', $invoice) }}">رجوع للفاتورة</a>
        </x-slot:actions>
    </x-invoice-sheet>
</div>
<script>
    (function () {
        var printed = false;
        function openDialog() {
            if (printed) return;
            printed = true;
            window.print();
        }
        document.fonts.ready.then(openDialog);
        setTimeout(openDialog, 800);
    })();
</script>
</body>
</html>
