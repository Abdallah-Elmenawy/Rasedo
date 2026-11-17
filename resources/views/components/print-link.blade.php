@props([
    'href' => null,
    'title' => 'طباعة',
])

@if($href)
    <button
        type="button"
        data-print-url="{{ $href }}"
        title="{{ $title }}"
        {{ $attributes->merge(['class' => 'btn btn-icon-action btn-icon-print']) }}
    >
        <i class="fe fe-printer"></i>
        <span class="sr-only">{{ $title }}</span>
    </button>
@else
    <button
        type="button"
        onclick="window.print()"
        title="{{ $title }}"
        {{ $attributes->merge(['class' => 'btn btn-icon-action btn-icon-print']) }}
    >
        <i class="fe fe-printer"></i>
        <span class="sr-only">{{ $title }}</span>
    </button>
@endif
