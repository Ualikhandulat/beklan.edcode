@extends('layouts.public')
@section('title', 'Добро пожаловать')

@php
    $subjectStyles = [
        ['icon' => 'calculator', 'bg' => 'bg-primary-light', 'text' => 'text-primary'],
        ['icon' => 'beaker', 'bg' => 'bg-info-light', 'text' => 'text-info'],
        ['icon' => 'book-open', 'bg' => 'bg-success-light', 'text' => 'text-success'],
        ['icon' => 'academic-cap', 'bg' => 'bg-danger-light', 'text' => 'text-danger'],
    ];
@endphp

@section('content')

{{-- ── Hero ────────────────────────────────────────────────────────────── --}}
<section class="relative overflow-hidden">
    <div class="absolute -top-24 -right-32 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-32 -left-24 w-80 h-80 bg-info/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="relative max-w-6xl mx-auto px-6 pt-16 pb-20 lg:pt-24 lg:pb-28 grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
        <div class="text-center lg:text-left">
            <span class="inline-flex items-center gap-2 bg-white border border-border rounded-full px-4 py-1.5 text-xs font-bold text-text-muted shadow-sm mb-5">
                <span class="w-2 h-2 rounded-full bg-success"></span>
                Платформа онлайн-тестирования для школьников
            </span>

            <h1 class="text-4xl sm:text-5xl font-extrabold text-text mb-5 leading-tight">
                Учись. <span class="text-primary">Проверяй.</span> Развивайся.
            </h1>

            <p class="text-lg text-text-muted mb-8 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                EdCode — платформа для прохождения тестов по школьным предметам и подготовки к ЕНТ.
                Получай доступ от преподавателя, проходи тесты в своём темпе и отслеживай свой прогресс.
            </p>

            <div class="flex flex-col sm:flex-row items-center gap-3 justify-center lg:justify-start">
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-full sm:w-auto">
                    <x-icon name="lock-closed" class="w-4 h-4" />
                    Войти в систему
                </a>
                <a href="#how-it-works" class="btn btn-outline btn-lg w-full sm:w-auto">
                    Как это работает
                    <x-icon name="arrow-right" class="w-4 h-4" />
                </a>
            </div>

            <div class="flex items-center justify-center lg:justify-start gap-8 mt-10 pt-8 border-t border-border">
                <div>
                    <p class="text-2xl font-extrabold text-text">{{ $stats['students'] }}+</p>
                    <p class="text-xs text-text-muted font-semibold mt-0.5">учеников</p>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-text">{{ $stats['subjects'] }}</p>
                    <p class="text-xs text-text-muted font-semibold mt-0.5">предметов</p>
                </div>
                <div>
                    <p class="text-2xl font-extrabold text-text">{{ $stats['groups'] }}</p>
                    <p class="text-xs text-text-muted font-semibold mt-0.5">групп</p>
                </div>
            </div>
        </div>

        <div class="relative hidden lg:flex items-center justify-center">
            <div class="card flex items-center justify-center p-10 max-w-sm w-full" style="box-shadow: var(--shadow-dropdown)">
                <img src="{{ asset('images/logo.png') }}" alt="EdCode" class="w-full max-w-[260px] rounded-2xl">
            </div>
        </div>
    </div>
</section>

{{-- ── Features ────────────────────────────────────────────────────────── --}}
<section id="features" class="max-w-6xl mx-auto px-6 py-16">
    <div class="text-center max-w-2xl mx-auto mb-10">
        <p class="text-xs font-extrabold text-primary uppercase tracking-widest mb-2">Возможности</p>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-text mb-3">Всё для удобной подготовки</h2>
        <p class="text-text-muted">Один аккаунт — доступ к тестам, прогрессу и истории прохождений.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card text-center transition-all duration-200 hover:-translate-y-1">
            <div class="w-12 h-12 rounded-2xl bg-primary-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="book-open" class="w-6 h-6 text-primary" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Разнообразные предметы</h3>
            <p class="text-sm text-text-muted leading-relaxed">Математика, физика, история и другие школьные дисциплины и предметы ЕНТ — в одном месте.</p>
        </div>

        <div class="card text-center transition-all duration-200 hover:-translate-y-1">
            <div class="w-12 h-12 rounded-2xl bg-info-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="chart-bar" class="w-6 h-6 text-info" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Отслеживание прогресса</h3>
            <p class="text-sm text-text-muted leading-relaxed">Мгновенные результаты по каждому тесту и полная история прохождений с баллами по темам.</p>
        </div>

        <div class="card text-center transition-all duration-200 hover:-translate-y-1">
            <div class="w-12 h-12 rounded-2xl bg-success-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="clock" class="w-6 h-6 text-success" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Тесты с таймером</h3>
            <p class="text-sm text-text-muted leading-relaxed">Прохождение в отведённое время — как в реальном экзамене, с автоматической проверкой.</p>
        </div>

        <div class="card text-center transition-all duration-200 hover:-translate-y-1">
            <div class="w-12 h-12 rounded-2xl bg-danger-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="shield-check" class="w-6 h-6 text-danger" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Надёжная система</h3>
            <p class="text-sm text-text-muted leading-relaxed">Доступ выдаётся преподавателем — никаких случайных попыток и потерянных результатов.</p>
        </div>

        <div class="card text-center transition-all duration-200 hover:-translate-y-1">
            <div class="w-12 h-12 rounded-2xl bg-primary-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="user-group" class="w-6 h-6 text-primary" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Групповое обучение</h3>
            <p class="text-sm text-text-muted leading-relaxed">Преподаватель видит успеваемость всей группы и каждого ученика в отдельности.</p>
        </div>

        <div class="card text-center transition-all duration-200 hover:-translate-y-1">
            <div class="w-12 h-12 rounded-2xl bg-info-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="academic-cap" class="w-6 h-6 text-info" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Подготовка к ЕНТ</h3>
            <p class="text-sm text-text-muted leading-relaxed">Отдельный формат тестов по предметам ЕНТ — отрабатывай реальный формат экзамена.</p>
        </div>
    </div>
</section>

{{-- ── How it works ────────────────────────────────────────────────────── --}}
<section id="how-it-works" class="bg-white border-y border-border">
    <div class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <p class="text-xs font-extrabold text-primary uppercase tracking-widest mb-2">Процесс</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text mb-3">Как это работает</h2>
            <p class="text-text-muted">Три простых шага от получения доступа до результата.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="relative">
                <div class="card h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center text-sm font-extrabold shrink-0">1</span>
                        <x-icon name="lock-closed" class="w-5 h-5 text-text-muted" />
                    </div>
                    <h3 class="font-extrabold text-text mb-2">Получи доступ</h3>
                    <p class="text-sm text-text-muted leading-relaxed">Преподаватель назначает тебе или твоей группе доступ к нужным предметам и тестам.</p>
                </div>
            </div>

            <div class="relative">
                <div class="card h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center text-sm font-extrabold shrink-0">2</span>
                        <x-icon name="play" class="w-5 h-5 text-text-muted" />
                    </div>
                    <h3 class="font-extrabold text-text mb-2">Пройди тест</h3>
                    <p class="text-sm text-text-muted leading-relaxed">Отвечай на вопросы в удобное время, в рамках лимита по времени, как на реальном экзамене.</p>
                </div>
            </div>

            <div class="relative">
                <div class="card h-full">
                    <div class="flex items-center gap-3 mb-4">
                        <span class="w-9 h-9 rounded-xl bg-primary text-white flex items-center justify-center text-sm font-extrabold shrink-0">3</span>
                        <x-icon name="chart-bar" class="w-5 h-5 text-text-muted" />
                    </div>
                    <h3 class="font-extrabold text-text mb-2">Узнай результат</h3>
                    <p class="text-sm text-text-muted leading-relaxed">Сразу получай баллы по каждой теме и следи за своим прогрессом в личном кабинете.</p>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── Subjects ────────────────────────────────────────────────────────── --}}
@if ($subjects->isNotEmpty())
    <section id="subjects" class="max-w-6xl mx-auto px-6 py-16">
        <div class="text-center max-w-2xl mx-auto mb-10">
            <p class="text-xs font-extrabold text-primary uppercase tracking-widest mb-2">Предметы</p>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-text mb-3">Чему можно учиться</h2>
            <p class="text-text-muted">Школьные дисциплины и предметы для подготовки к ЕНТ.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach ($subjects as $i => $subject)
                @php $style = $subjectStyles[$i % count($subjectStyles)]; @endphp
                <div class="card card-sm flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $style['bg'] }} flex items-center justify-center shrink-0">
                        <x-icon :name="$style['icon']" class="w-5 h-5 {{ $style['text'] }}" />
                    </div>
                    <p class="text-sm font-bold text-text leading-snug">{{ $subject->title }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endif

{{-- ── CTA ─────────────────────────────────────────────────────────────── --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    <div class="card bg-primary border-primary text-center py-12 px-6 sm:px-12">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-white mb-3">Готов проверить свои знания?</h2>
        <p class="text-white/80 max-w-xl mx-auto mb-7">
            Войди в систему по логину и паролю, который выдал преподаватель, и приступай к тестам.
        </p>
        <a href="{{ route('login') }}" class="btn btn-lg bg-white text-primary hover:bg-gray-50">
            <x-icon name="arrow-right" class="w-4 h-4" />
            Войти в систему
        </a>
    </div>
</section>

@endsection
