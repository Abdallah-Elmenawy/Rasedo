<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title> </title>
    <style>
        body { margin: 0; font-family: "Cairo", Tahoma, sans-serif; color: #1a2744; }
        .sheet { width: 320px; margin: 24px auto; text-align: center; }
        h1 { margin: 0 0 4px; font-size: 18px; }
        p { margin: 0 0 12px; color: #1E4E9B; font-weight: 700; letter-spacing: 1px; }
        img { display: block; margin: 0 auto 14px; max-width: 100%; }
        @media print { .sheet { margin: 0 auto; } }
    </style>
</head>
<body>
    <div class="sheet">
        <h1>{{ $product->name }}</h1>
        <p>{{ $product->scan_code }}</p>
        <img src="{{ route('products.barcode', $product) }}" alt="Barcode">
        <img src="{{ route('products.qr', $product) }}" alt="QR" width="160" height="160">
    </div>
    <script>
        window.addEventListener('load', function () { window.print(); });
    </script>
</body>
</html>
