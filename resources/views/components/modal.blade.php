@props([
    'title',
    'size'  => 'md',
    'show'  => 'open',
])
@php
    $widthClass = match ($size) {
        'sm'  => 'max-w-sm',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-2xl',
        '2xl' => 'max-w-5xl',
        default => 'max-w-md',
    };
@endphp

<div x-show="{{ $show }}"
     x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center p-4"
     style="background: rgba(0,0,0,0.45)"
     x-transition:enter="transition ease-out duration-200"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="transition ease-in duration-150"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     @click.self="{{ $show }} = false"
     @keydown.escape.window="{{ $show }} = false">

    <div class="bg-white rounded-2xl {{ $widthClass }} w-full flex flex-col"
         style="max-height: 82vh; box-shadow: var(--shadow-dropdown)"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.stop>

        {{-- Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-border shrink-0">
            <p class="font-extrabold text-text">{{ $title }}</p>
            <button @click="{{ $show }} = false"
                    class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light">
                <x-icon name="x" class="w-4 h-4" />
            </button>
        </div>

        {{-- Body --}}
        <div class="flex-1 overflow-y-auto custom-scrollbar min-h-0">
            {{ $slot }}
        </div>

        {{-- Footer (optional named slot) --}}
        @isset($footer)
            <div class="px-5 py-4 border-t border-border shrink-0 flex items-center justify-end gap-2">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
