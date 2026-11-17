@props([
    'name',
    'id' => null,
    'accept' => null,
    'placeholder' => 'اختر ملفاً',
    'icon' => 'fe-upload-cloud',
])
@php
    $id = $id ?? 'file_'.\Illuminate\Support\Str::slug($name, '_').'_'.\Illuminate\Support\Str::random(4);
    $invalid = $errors->has($name);
@endphp
<label class="file-drop {{ $invalid ? 'is-invalid' : '' }}" for="{{ $id }}">
    <i class="fe {{ $icon }}"></i>
    <span class="file-drop-name" data-placeholder="{{ $placeholder }}">{{ $placeholder }}</span>
    <span class="file-drop-btn">استعراض</span>
    <input {{ $attributes->merge(['id' => $id, 'type' => 'file', 'name' => $name, 'accept' => $accept]) }}>
</label>
