@push('header-title')
    <nav class="header-page-title" aria-label="مسار الصفحة">
        <a href="{{ route('dashboard') }}">
            <i class="fe fe-home"></i>
            <span>الرئيسيه</span>
        </a>
        @if(!empty($title) && $title !== 'لوحة التحكم')
            <span class="header-crumb-sep">/</span>
            <span class="header-crumb-current">{{ $title }}</span>
        @endif
    </nav>
@endpush

@php
    $actionHtml = $action ?? '';
    $exportItems = $exports ?? ($export ?? null);
    if (is_string($exportItems) && $exportItems !== '') {
        $exportItems = [$exportItems];
    } elseif (is_array($exportItems) && ! array_is_list($exportItems)) {
        $exportItems = [$exportItems];
    } elseif (! is_array($exportItems)) {
        $exportItems = [];
    }
@endphp
@if($actionHtml !== '' || $exportItems !== [])
    <div class="breadcrumb-header justify-content-between">
        <div class="page-header-action">
            {!! $actionHtml !!}
            @foreach($exportItems as $item)
                @if(is_array($item))
                    <x-export-excel :sheet="$item['sheet']" :params="$item['params'] ?? []" :label="$item['label'] ?? 'Excel'" />
                @else
                    <x-export-excel :sheet="$item" />
                @endif
            @endforeach
        </div>
    </div>
@endif
