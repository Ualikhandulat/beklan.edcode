@props([
    'name',
    'label'       => null,
    'type'        => 'text',
    'placeholder' => '',
    'value'       => '',
    'required'    => false,
])

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">
            {{ $label }}
            @if ($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    >

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
