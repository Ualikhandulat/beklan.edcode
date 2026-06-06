@extends('layouts.student')
@section('title', 'История тестов')

@section('content')

<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.4s ease both; }
</style>

<div class="flex items-center justify-between mb-4 fade-up">
    <h2 class="text-base font-extrabold text-text">История тестов</h2>
    @if ($tests->isNotEmpty())
        <span class="text-xs font-bold text-text-muted bg-white border border-border px-2.5 py-1 rounded-full shadow-sm">
            {{ $tests->count() }}
        </span>
    @endif
</div>

@if ($tests->isEmpty())
    <div class="card text-center py-16 fade-up" style="animation-delay: 60ms">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <x-icon name="chart-bar" class="w-7 h-7 text-text-muted" />
        </div>
        <p class="font-extrabold text-text mb-1">Пока нет результатов</p>
        <p class="text-sm text-text-muted max-w-xs mx-auto leading-relaxed">
            После прохождения первого теста здесь появится история результатов.
        </p>
        <a href="{{ route('student.tests.active') }}"
           class="inline-flex items-center gap-1.5 mt-5 text-sm font-bold text-primary hover:underline">
            Перейти к тестам
            <x-icon name="arrow-right" class="w-3.5 h-3.5" />
        </a>
    </div>
@else
    <div class="space-y-3">
        @foreach ($tests as $i => $test)
            @php
                $access  = $test->access;
                $pct     = $test->max_score > 0 ? round($test->total_score / $test->max_score * 100) : 0;
                $isGood  = $pct >= 70;
                $isMid   = $pct >= 50 && $pct < 70;
                $color   = $isGood ? 'var(--color-success)' : ($isMid ? 'var(--color-primary)' : 'var(--color-danger)');
                $bgClass = $isGood ? 'bg-success/10 text-success' : ($isMid ? 'bg-primary/10 text-primary' : 'bg-danger/10 text-danger');

                $isEnt  = $access && $access->type === \App\Enums\TestAccessType::Ent;
                $single = $access ? ($isEnt ? null : $access->accessSubjects->first()) : null;
                $title  = $isEnt
                    ? 'ЕНТ'
                    : ($single?->subject?->title ?? ($access?->type->label() ?? 'Тест'));

                $duration = $test->started_at
                    ? $test->started_at->diffInSeconds($test->completed_at)
                    : null;
                $durationStr = null;
                if ($duration !== null) {
                    $h = intdiv($duration, 3600);
                    $m = intdiv($duration % 3600, 60);
                    $s = $duration % 60;
                    $durationStr = $h > 0 ? "{$h}ч {$m}м" : ($m > 0 ? "{$m}м {$s}с" : "{$s}с");
                }
            @endphp

            <div class="fade-up bg-white rounded-2xl border border-border overflow-hidden flex"
                 style="animation-delay: {{ 60 + $i * 50 }}ms; box-shadow: var(--shadow-card)">

                {{-- Score bar --}}
                <div class="w-1 shrink-0" style="background: {{ $color }}"></div>

                <div class="flex-1 p-4 sm:p-5 min-w-0">
                    <div class="flex items-start gap-3">

                        {{-- Score circle --}}
                        <div class="relative w-14 h-14 shrink-0">
                            <svg class="w-14 h-14 -rotate-90" viewBox="0 0 36 36">
                                <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                                <circle cx="18" cy="18" r="14" fill="none" stroke="{{ $color }}"
                                        stroke-width="3"
                                        stroke-linecap="round"
                                        stroke-dasharray="{{ round($pct * 0.88, 1) }} 88"/>
                            </svg>
                            <span class="absolute inset-0 flex items-center justify-center text-[11px] font-extrabold"
                                  style="color: {{ $color }}">{{ $pct }}%</span>
                        </div>

                        {{-- Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="font-extrabold text-text text-sm leading-snug truncate">{{ $title }}</p>
                                    <p class="text-xs text-text-muted mt-0.5">
                                        Попытка {{ $test->attempt_number }}
                                        @if ($test->completed_at)
                                            · {{ $test->completed_at->format('d.m.Y H:i') }}
                                        @endif
                                    </p>
                                </div>
                                <span class="text-lg font-black tabular-nums shrink-0 leading-none" style="color: {{ $color }}">
                                    {{ $test->total_score }}<span class="text-xs text-text-muted font-semibold">/{{ $test->max_score }}</span>
                                </span>
                            </div>

                            <div class="flex items-center gap-3 mt-3 pt-3 border-t border-border/60">
                                @if ($durationStr !== null)
                                    <span class="text-xs text-text-muted flex items-center gap-1">
                                        <x-icon name="clock" class="w-3 h-3" />
                                        {{ $durationStr }}
                                    </span>
                                @endif
                                <div class="ml-auto flex items-center gap-2">
                                    <a href="{{ route('student.test.result', $test) }}"
                                       class="text-xs font-bold text-text-muted hover:text-text px-2.5 py-1.5 rounded-lg hover:bg-gray-100 transition-colors">
                                        Результат
                                    </a>
                                    <a href="{{ route('student.test.detail', $test) }}"
                                       class="inline-flex items-center gap-1 text-xs font-extrabold px-3 py-1.5 rounded-xl text-white transition-all duration-200 hover:shadow-md"
                                       style="background: var(--color-primary)">
                                        <x-icon name="book-open" class="w-3.5 h-3.5" />
                                        Разбор
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
