@props([
    'name',
    'label'    => null,
    'options'  => [],
    'selected' => null,
    'required' => false,
    'placeholder' => null,
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

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes }}
    >
        @if ($placeholder)
            <option value="" disabled @selected(! old($name, $selected))>{{ $placeholder }}</option>
        @endif

        @foreach ($options as $value => $text)
            <option value="{{ $value }}" @selected(old($name, $selected) == $value)>{{ $text }}</option>
        @endforeach
    </select>

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
