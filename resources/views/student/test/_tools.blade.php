@php
    $toolBtn = 'flex items-center gap-1.5 px-3 py-2 rounded-xl border border-border bg-white text-xs font-bold text-text-muted hover:border-primary/40 hover:text-primary transition-colors shrink-0 cursor-pointer max-w-[7.5rem] lg:max-w-none';
@endphp
<div class="flex items-center gap-2 overflow-x-auto">
    <button type="button" @click="calcOpen = true" class="{{ $toolBtn }}">
        <x-icon name="calculator" class="w-4 h-4 shrink-0" />
        <span class="truncate min-w-0">{{ __('Калькулятор') }}</span>
    </button>
    <button type="button" @click="mendeleevOpen = true" class="{{ $toolBtn }}">
        <x-icon name="beaker" class="w-4 h-4 shrink-0" />
        <span class="truncate min-w-0">{{ __('Менделеев') }}</span>
    </button>
    <button type="button" @click="solubilityOpen = true" class="{{ $toolBtn }}">
        <x-icon name="beaker" class="w-4 h-4 shrink-0" />
        <span class="truncate min-w-0">{{ __('Растворимость') }}</span>
    </button>
</div>
