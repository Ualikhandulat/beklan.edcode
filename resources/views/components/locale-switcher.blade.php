@php
    $locales = ['ru' => 'РУС', 'kk' => 'ҚАЗ'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-1 bg-gray-100 rounded-full p-0.5']) }}>
    @foreach ($locales as $code => $label)
        <form method="POST" action="{{ route('locale.update', $code) }}">
            @csrf
            <button type="submit"
                    class="px-2.5 py-1 rounded-full text-xs font-bold transition-colors cursor-pointer {{ app()->getLocale() === $code ? 'bg-white text-primary shadow-sm' : 'text-text-muted hover:text-text' }}">
                {{ $label }}
            </button>
        </form>
    @endforeach
</div>
