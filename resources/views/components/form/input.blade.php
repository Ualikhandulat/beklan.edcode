@props([
    'name',
    'label'       => null,
    'type'        => 'text',
    'placeholder' => '',
    'value'       => null,
    'required'    => false,
    'readonly'    => false,
    'disabled'    => false,
])

@php
    $hasError    = $errors->has($name);
    $borderClass = $hasError ? 'border-danger focus:border-danger focus:ring-danger/20' : '';
    $stateClass  = $readonly ? 'cursor-default opacity-70 bg-gray-50' : ($disabled ? 'cursor-not-allowed opacity-50 bg-gray-100' : '');
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">
            {{ $label }}
            @if ($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required @endif
        @if ($readonly) readonly @endif
        @if ($disabled) disabled @endif
        {{ $attributes->merge(['class' => trim("$borderClass $stateClass")]) }}
    >

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
