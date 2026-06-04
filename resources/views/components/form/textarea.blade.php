@props([
    'name',
    'label'       => null,
    'placeholder' => '',
    'value'       => null,
    'rows'        => 4,
    'required'    => false,
    'disabled'    => false,
])

@php
    $hasError = $errors->has($name);
    $borderClass = $hasError ? 'border-danger' : '';
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => $borderClass]) }}
    >{{ old($name, $value) }}</textarea>

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
