@props([
    'name',
    'label'       => null,
    'options'     => [],  // ['value' => 'label'] or ['value' => ['label' => '...', 'subtitle' => '...']]
    'selected'    => null,
    'placeholder' => 'Выберите...',
    'searchable'  => true,
    'disabled'    => false,
    'required'    => false,
])

@php
    $hasError   = $errors->has($name);
    $hoverClass = $hasError ? '' : 'hover:border-gray-400';

    $formatted = [];
    foreach ($options as $key => $option) {
        $formatted[(string) $key] = is_array($option)
            ? ['label' => $option['label'] ?? '', 'subtitle' => $option['subtitle'] ?? '']
            : ['label' => $option, 'subtitle' => ''];
    }

    $resolved     = (string) old($name, $selected ?? '');
    $initialLabel = $placeholder;
    if ($resolved !== '' && isset($formatted[$resolved])) {
        $initialLabel = $formatted[$resolved]['label'];
    }
@endphp

<div class="form-group">
    @if ($label)
        <label for="{{ $name }}">
            {{ $label }}
            @if ($required) <span class="text-danger">*</span> @endif
        </label>
    @endif

    <div x-data="{
            open: false,
            dropUp: false,
            value: @js($resolved),
            label: @js($initialLabel),
            search: '',
            disabled: {{ $disabled ? 'true' : 'false' }},
            allOptions: @js($formatted),
            toggle() {
                if (this.disabled) return;

                if (!this.open) {
                    // Открываем вверх, только если внизу места меньше, чем нужно дропдауну, и сверху его больше
                    const rect = this.$refs.trigger.getBoundingClientRect();
                    const spaceBelow = window.innerHeight - rect.bottom;
                    const spaceAbove = rect.top;
                    this.dropUp = spaceBelow < 300 && spaceAbove > spaceBelow;
                }

                this.open = !this.open;

                if (this.open && {{ $searchable ? 'true' : 'false' }}) {
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                }
            },
            get filteredOptions() {
                if (!this.search) return this.allOptions;
                const s = this.search.toLowerCase();
                return Object.fromEntries(
                    Object.entries(this.allOptions).filter(([, o]) =>
                        o.label.toLowerCase().includes(s) || o.subtitle.toLowerCase().includes(s)
                    )
                );
            },
            select(val, lab) {
                if (this.disabled) return;
                this.value = val;
                this.label = lab;
                this.open  = false;
                this.search = '';
            }
        }" class="relative" @click.outside="open = false">

        <input type="hidden" name="{{ $name }}" x-model="value">

        {{-- Trigger --}}
        <button type="button"
            x-ref="trigger"
            @click="toggle()"
            :disabled="disabled"
            class="w-full flex items-center justify-between px-4 py-2.5 bg-white border rounded-2xl text-sm transition-colors {{ $hasError ? 'border-danger' : 'border-border' }}"
            :class="[
                open ? 'border-primary ring-2 ring-primary/20 outline-none' : '{{ $hoverClass }}',
                disabled ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'
            ]">
            <span x-text="label" :class="value === '' ? 'text-text-muted' : 'text-text'" class="truncate pr-2"></span>
            {{-- x-bind:class вместо :class — иначе Blade пытается вычислить как PHP --}}
            <x-icon name="chevron-down" class="w-4 h-4 text-text-muted shrink-0 transition-transform duration-200" x-bind:class="open ? 'rotate-180' : ''" />
        </button>

        {{-- Dropdown --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-100"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="absolute z-50 w-full bg-white border border-border rounded-2xl overflow-hidden"
             :class="dropUp ? 'bottom-full mb-1.5 origin-bottom' : 'top-full mt-1.5 origin-top'"
             style="display:none; box-shadow: var(--shadow-dropdown)">

            @if ($searchable)
            <div class="p-2 border-b border-border bg-gray-50">
                <div class="relative">
                    <x-icon name="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-text-muted pointer-events-none" />
                    <input type="text"
                           x-ref="searchInput"
                           x-model="search"
                           @click.stop
                           @keydown.enter.prevent="const e = Object.entries(filteredOptions); if (e.length > 0) { const [val, opt] = e[0]; select(val, opt.label) }"
                           placeholder="Поиск..."
                           class="w-full pl-8 pr-3 py-1.5 text-xs border border-border rounded-xl focus:outline-none focus:border-primary bg-white">
                </div>
            </div>
            @endif

            <div class="max-h-56 overflow-y-auto custom-scrollbar">
                <template x-for="(opt, val) in filteredOptions" :key="val">
                    <button type="button"
                            @click="select(val, opt.label)"
                            class="w-full text-left px-4 py-2.5 text-sm transition-colors flex items-center justify-between gap-3 cursor-pointer"
                            :class="value === val ? 'bg-primary-light text-primary font-bold' : 'text-text hover:bg-gray-50'">
                        <div class="min-w-0">
                            <span x-text="opt.label" class="block truncate"></span>
                            <span x-show="opt.subtitle" x-text="opt.subtitle" class="block text-xs opacity-60 mt-0.5 truncate"></span>
                        </div>
                        <x-icon name="check" class="w-4 h-4 shrink-0 text-primary" x-show="value === val" />
                    </button>
                </template>

                <div x-show="Object.keys(filteredOptions).length === 0"
                     class="px-4 py-6 text-center text-sm text-text-muted">
                    Ничего не найдено
                </div>
            </div>
        </div>
    </div>

    @error($name)
        <p class="form-error">{{ $message }}</p>
    @enderror
</div>
