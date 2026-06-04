@props([
    'name',
    'label'   => null,
    'value'   => 1,
    'checked' => false,
    'disabled' => false,
])

<div class="form-group">
    <label class="inline-flex items-center gap-3 cursor-pointer {{ $disabled ? 'opacity-60 cursor-not-allowed' : '' }} mb-0">
        <div class="relative shrink-0">
            <input type="hidden" name="{{ $name }}" value="0" @if($disabled) disabled @endif>
            <input
                type="checkbox"
                name="{{ $name }}"
                value="{{ $value }}"
                class="sr-only peer"
                @if($checked) checked @endif
                @if($disabled) disabled @endif
            >
            <div class="w-11 h-6 bg-gray-200 peer-checked:bg-success rounded-full transition-all duration-300 shadow-inner border border-gray-200 peer-focus:ring-2 peer-focus:ring-success/30"></div>
            <div class="absolute top-[2px] left-[2px] bg-white rounded-full h-5 w-5 shadow-md transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] peer-checked:translate-x-full"></div>
        </div>
        @if ($label)
            <span class="text-sm font-semibold text-text select-none">{{ $label }}</span>
        @endif
    </label>
    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
