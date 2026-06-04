@props([
    'name',
    'label'       => null,
    'placeholder' => '',
    'rows'        => 4,
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

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    >{{ old($name) }}</textarea>

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
