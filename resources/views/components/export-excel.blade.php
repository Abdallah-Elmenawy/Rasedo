@props(['sheet', 'params' => [], 'label' => 'Excel'])

@php
    $query = collect(array_merge(request()->except('page'), $params))
        ->filter(fn ($value) => $value !== null && $value !== '')
        ->all();
@endphp

<a href="{{ route('exports.download', array_merge(['sheet' => $sheet], $query)) }}" class="btn-excel">
    <i class="fe fe-download"></i>
    <span>{{ $label }}</span>
</a>
