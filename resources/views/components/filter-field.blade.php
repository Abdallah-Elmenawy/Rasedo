@props(['label', 'name', 'type' => 'text', 'options' => [], 'placeholder' => 'أدخل القيمة...', 'value' => null])

<div class="filter-field">
    <label>{{ $label }}</label>
    @if($type === 'select')
        <select name="{{ $name }}" class="form-control">
            <option value="">الكل</option>
            @foreach($options as $value => $text)
                <option value="{{ $value }}" @selected((string) request($name) === (string) $value)>{{ $text }}</option>
            @endforeach
        </select>
    @else
        <div class="filter-control">
            <input
                type="{{ $type === 'date' ? 'date' : ($type === 'number' ? 'number' : 'text') }}"
                name="{{ $name }}"
                value="{{ $value ?? request($name) }}"
                class="form-control"
                placeholder="{{ $placeholder }}"
                @if($type === 'number') step="any" @endif
            >
            <button type="button" class="filter-clear" hidden aria-label="مسح"><i class="fe fe-x"></i></button>
        </div>
    @endif
</div>
