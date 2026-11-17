<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0, user-scalable=0'>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>شاشة البيع</title>
    @include('layouts.head')
</head>
<body class="pos-standalone cairo">
    <div id="global-loader">
        <img src="{{ URL::asset('assets/img/loader.svg') }}" class="loader-img" alt="Loader">
    </div>
    @include('admin.partials.alerts')
    @yield('content')
    @include('layouts.footer-scripts')
</body>
</html>
