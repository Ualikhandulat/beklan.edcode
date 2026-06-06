@extends('layouts.student')
@section('title', 'О платформе')

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
            Платформа онлайн-тестирования для подготовки к ЕНТ и предметным экзаменам.
            Проходи тесты, отслеживай результаты и работай над ошибками.
        </p>
    </div>
</div>

{{-- Features --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @php
        $features = [
            ['icon' => 'clipboard-list', 'color' => 'primary',  'title' => 'Онлайн-тесты',      'text' => 'ЕНТ, предметные и тематические тесты в удобном интерфейсе.'],
            ['icon' => 'chart-bar',      'color' => 'info',     'title' => 'Результаты сразу',   'text' => 'Мгновенный подсчёт баллов и детальный разбор каждого вопроса.'],
            ['icon' => 'refresh',        'color' => 'success',  'title' => 'Работа над ошибками','text' => 'Просматривай разбор после каждого теста и учись на своих ошибках.'],
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
    <h2 class="text-base font-extrabold text-text mb-4">Как пользоваться</h2>
    <div class="space-y-4">
        @php
            $steps = [
                ['num' => '1', 'title' => 'Активные тесты',       'text' => 'На вкладке «Активные» отображаются тесты, которые ты ещё не завершил или по которым остались попытки.'],
                ['num' => '2', 'title' => 'Начни тест',            'text' => 'Нажми «Начать тест», выбери нужный вариант или нұсқа (если требуется) и приступай к вопросам.'],
                ['num' => '3', 'title' => 'Навигация по вопросам', 'text' => 'Переключайся между предметами через вкладки в сайдбаре. Отвеченные вопросы отмечаются зелёным.'],
                ['num' => '4', 'title' => 'Работа над ошибками',   'text' => 'После завершения теста нажми «Разбор ответов» — увидишь все свои ответы с пометкой правильно/неправильно и верными ответами.'],
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
    <h2 class="text-base font-extrabold text-text mb-4">Контакты</h2>
    <div class="space-y-3">
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-primary/10 flex items-center justify-center shrink-0">
                <x-icon name="phone" class="w-4 h-4 text-primary" />
            </div>
            <div>
                <p class="text-xs text-text-muted font-semibold">Телефон</p>
                <a href="tel:+77001234567" class="text-sm font-bold text-text hover:text-primary transition-colors">
                    +7 700 123 45 67
                </a>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-info/10 flex items-center justify-center shrink-0">
                <x-icon name="mail" class="w-4 h-4 text-info" />
            </div>
            <div>
                <p class="text-xs text-text-muted font-semibold">Email</p>
                <a href="mailto:support@beklan.edcode.kz" class="text-sm font-bold text-text hover:text-primary transition-colors">
                    support@beklan.edcode.kz
                </a>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 rounded-xl bg-success/10 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4 text-success" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                    <path d="M12 0C5.374 0 0 5.373 0 12c0 2.117.549 4.107 1.508 5.84L.057 23.887a.5.5 0 0 0 .589.639l6.264-1.639A11.94 11.94 0 0 0 12 24c6.626 0 12-5.373 12-12S18.626 0 12 0zm0 21.818a9.817 9.817 0 0 1-5.013-1.374l-.36-.213-3.72.975.992-3.616-.234-.372A9.817 9.817 0 0 1 2.182 12C2.182 6.58 6.58 2.182 12 2.182S21.818 6.58 21.818 12 17.42 21.818 12 21.818z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-text-muted font-semibold">WhatsApp</p>
                <a href="https://wa.me/77001234567" target="_blank" class="text-sm font-bold text-text hover:text-primary transition-colors">
                    +7 700 123 45 67
                </a>
            </div>
        </div>
    </div>

    <div class="mt-4 pt-4 border-t border-border">
        <p class="text-xs text-text-muted">
            Работаем с 9:00 до 18:00 (пн–пт). По вопросам доступа к тестам обращайтесь к своему преподавателю.
        </p>
    </div>
</div>

@endsection
