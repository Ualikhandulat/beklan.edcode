@extends('layouts.student')
@section('title', __('О платформе'))

@section('content')

<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.4s ease both; }
</style>

{{-- Hero --}}
<div class="card mb-6 relative overflow-hidden fade-up">
    <div class="absolute inset-0 pointer-events-none"
         style="background: radial-gradient(ellipse 70% 60% at 50% -10%, color-mix(in srgb, var(--color-primary) 10%, transparent) 0%, transparent 70%)"></div>
    <div class="relative text-center py-6">
        <div class="inline-flex items-center gap-0.5 mb-4">
            <span class="text-4xl font-black text-primary">Ed</span><span class="text-4xl font-black text-text">Code</span>
        </div>
        <p class="text-text-muted text-sm font-semibold max-w-md mx-auto leading-relaxed">
            {{ __('Платформа онлайн-тестирования для подготовки к ЕНТ и предметным экзаменам. Проходи тесты, отслеживай результаты и работай над ошибками.') }}
        </p>
    </div>
</div>

{{-- Features --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @php
        $features = [
            ['icon' => 'clipboard-list', 'color' => 'primary',  'title' => __('Онлайн-тесты'),      'text' => __('ЕНТ, предметные и тематические тесты в удобном интерфейсе.')],
            ['icon' => 'chart-bar',      'color' => 'info',     'title' => __('Результаты сразу'),   'text' => __('Мгновенный подсчёт баллов и детальный разбор каждого вопроса.')],
            ['icon' => 'refresh',        'color' => 'success',  'title' => __('Работа над ошибками'),'text' => __('Просматривай разбор после каждого теста и учись на своих ошибках.')],
        ];
    @endphp
    @foreach ($features as $i => $f)
        <div class="card text-center fade-up" style="animation-delay: {{ 60 + $i * 60 }}ms">
            <div class="w-11 h-11 rounded-xl mx-auto mb-3 flex items-center justify-center"
                 style="background: var(--color-{{ $f['color'] }}-light)">
                <x-icon name="{{ $f['icon'] }}" class="w-5 h-5" style="color: var(--color-{{ $f['color'] }})" />
            </div>
            <p class="text-sm font-extrabold text-text mb-1">{{ $f['title'] }}</p>
            <p class="text-xs text-text-muted leading-relaxed">{{ $f['text'] }}</p>
        </div>
    @endforeach
</div>

{{-- How it works --}}
<div class="card mb-6 fade-up" style="animation-delay: 240ms">
    <h2 class="text-base font-extrabold text-text mb-4">{{ __('Как пользоваться') }}</h2>
    <div class="space-y-4">
        @php
            $steps = [
                ['num' => '1', 'title' => __('Активные тесты'),       'text' => __('На вкладке «Активные» отображаются тесты, которые ты ещё не завершил или по которым остались попытки.')],
                ['num' => '2', 'title' => __('Начни тест'),            'text' => __('Нажми «Начать тест», выбери нужный вариант или нұсқа (если требуется) и приступай к вопросам.')],
                ['num' => '3', 'title' => __('Навигация по вопросам'), 'text' => __('Переключайся между предметами через вкладки в сайдбаре. Отвеченные вопросы отмечаются зелёным.')],
                ['num' => '4', 'title' => __('Работа над ошибками'),   'text' => __('После завершения теста нажми «Разбор ответов» — увидишь все свои ответы с пометкой правильно/неправильно и верными ответами.')],
            ];
        @endphp
        @foreach ($steps as $step)
            <div class="flex items-start gap-3">
                <span class="w-7 h-7 rounded-xl flex items-center justify-center text-xs font-extrabold text-white shrink-0 mt-0.5"
                      style="background: var(--color-primary)">{{ $step['num'] }}</span>
                <div>
                    <p class="text-sm font-extrabold text-text leading-snug">{{ $step['title'] }}</p>
                    <p class="text-xs text-text-muted mt-0.5 leading-relaxed">{{ $step['text'] }}</p>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Contacts --}}
<div class="card fade-up" style="animation-delay: 300ms">
    <h2 class="text-base font-extrabold text-text mb-4">{{ __('Контакты') }}</h2>
    <div class="space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                <x-icon name="phone" class="w-4 h-4 text-primary" />
            </div>
            <div>
                <p class="text-xs text-text-muted font-semibold">{{ __('Телефон') }}</p>
                <a href="tel:+77718861404" class="text-sm font-bold text-text hover:text-primary transition-colors">
                    +7 771 886 1404
                </a>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                <x-icon name="whatsapp" class="w-4 h-4 text-success" />
            </div>
            <div>
                <p class="text-xs text-text-muted font-semibold">WhatsApp</p>
                <a href="https://wa.me/77718861404" target="_blank" class="text-sm font-bold text-text hover:text-primary transition-colors">
                    +7 771 886 1404
                </a>
            </div>
        </div>
    </div>

    <div class="mt-4 pt-4 border-t border-border">
        <p class="text-xs text-text-muted">
            {{ __('Работаем с 9:00 до 18:00 (пн–пт). Для получения доступа к тестам обратитесь к нам по телефону или WhatsApp выше.') }}
        </p>
    </div>
</div>

@endsection
