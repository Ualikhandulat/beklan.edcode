@extends('layouts.student')
@section('title', 'Результаты теста')

@section('content')

@php
    $pct = $test->max_score > 0 ? round($test->total_score / $test->max_score * 100) : 0;
    $isGood = $pct >= 70;
    $isMid  = $pct >= 50 && $pct < 70;
    $scoreColor = $isGood ? 'var(--color-success)' : ($isMid ? 'var(--color-primary)' : 'var(--color-danger)');
    $access = $test->access;
@endphp

<style>
    @keyframes scoreIn {
        from { transform: scale(0.6); opacity: 0; }
        to   { transform: scale(1); opacity: 1; }
    }
    .score-in { animation: scoreIn 0.5s cubic-bezier(0.34,1.56,0.64,1) both; animation-delay: 200ms; }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(12px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.4s ease both; }
</style>

{{-- Back --}}
<a href="{{ route('student.dashboard') }}"
   class="inline-flex items-center gap-1.5 text-sm text-text-muted hover:text-text mb-5 transition-colors fade-up">
    <x-icon name="arrow-left" class="w-4 h-4" />
    Вернуться в кабинет
</a>

{{-- Score hero --}}
<div class="card mb-5 text-center relative overflow-hidden fade-up" style="animation-delay: 50ms">

    <div class="absolute inset-0 pointer-events-none"
         style="background: radial-gradient(ellipse 70% 60% at 50% -10%, color-mix(in srgb, {{ $scoreColor }} 10%, transparent) 0%, transparent 70%)"></div>

    <div class="relative">
        <p class="text-xs font-extrabold uppercase tracking-widest text-text-muted mb-4">Результат теста</p>

        <div class="score-in inline-flex items-end gap-1 mb-2">
            <span class="text-7xl font-black leading-none tabular-nums" style="color: {{ $scoreColor }}">
                {{ $test->total_score }}
            </span>
            <span class="text-2xl font-black text-text-muted pb-2">/ {{ $test->max_score }}</span>
        </div>

        <div class="flex items-center justify-center gap-3 mb-5">
            <div class="h-2 w-48 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full transition-all duration-1000"
                     style="width: {{ $pct }}%; background: {{ $scoreColor }}"></div>
            </div>
            <span class="text-xl font-black tabular-nums" style="color: {{ $scoreColor }}">{{ $pct }}%</span>
        </div>

        <div class="flex flex-wrap items-center justify-center gap-4 text-sm text-text-muted">
            <div class="flex items-center gap-1.5">
                <x-icon name="clock" class="w-4 h-4 shrink-0" />
                @php
                    $duration = $test->started_at->diffInSeconds($test->completed_at);
                    $m = intdiv($duration, 60);
                    $s = $duration % 60;
                @endphp
                {{ $m }}м {{ $s }}с
            </div>
            <div class="flex items-center gap-1.5">
                <x-icon name="refresh" class="w-4 h-4 shrink-0" />
                Попытка {{ $test->attempt_number }}
            </div>
            <div class="flex items-center gap-1.5">
                <x-icon name="calendar" class="w-4 h-4 shrink-0" />
                {{ $test->completed_at->format('d.m.Y H:i') }}
            </div>
        </div>
    </div>
</div>

{{-- Per-subject breakdown --}}
<div class="space-y-4 mb-5">
    @foreach ($subjectsData as $i => $subject)
        @php
            $sPct   = $subject['max_score'] > 0 ? round($subject['score'] / $subject['max_score'] * 100) : 0;
            $sGood  = $sPct >= 70;
            $sMid   = $sPct >= 50 && $sPct < 70;
            $sColor = $sGood ? 'var(--color-success)' : ($sMid ? 'var(--color-primary)' : 'var(--color-danger)');
        @endphp

        <div class="card fade-up" style="animation-delay: {{ 100 + $i * 60 }}ms"
             x-data="{ open: false }">

            {{-- Subject header --}}
            <button type="button"
                    @click="open = !open"
                    class="w-full flex items-center gap-4 text-left">
                <div class="flex-1 min-w-0">
                    <p class="font-extrabold text-text text-sm">{{ $subject['subject']->title }}</p>
                    @if ($subject['part'])
                        <p class="text-xs text-text-muted">{{ $subject['part']->title }}</p>
                    @endif
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="text-right">
                        <p class="text-lg font-black tabular-nums leading-none" style="color: {{ $sColor }}">
                            {{ $subject['score'] }}
                        </p>
                        <p class="text-xs text-text-muted">из {{ $subject['max_score'] }}</p>
                    </div>
                    <div class="w-12 h-12 relative shrink-0">
                        <svg class="w-12 h-12 -rotate-90" viewBox="0 0 36 36">
                            <circle cx="18" cy="18" r="14" fill="none" stroke="#e5e7eb" stroke-width="3"/>
                            <circle cx="18" cy="18" r="14" fill="none" stroke="{{ $sColor }}"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-dasharray="{{ round($sPct * 0.88, 1) }} 88"/>
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-[10px] font-extrabold"
                              style="color: {{ $sColor }}">{{ $sPct }}%</span>
                    </div>
                    <span class="w-4 h-4 text-text-muted transition-transform duration-200 shrink-0 flex items-center"
                          :class="open ? 'rotate-180' : ''">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </span>
                </div>
            </button>

            {{-- Questions detail --}}
            <div x-show="open" x-transition class="mt-4 pt-4 border-t border-border">
                <div class="space-y-2">
                    @foreach ($subject['questions'] as $q)
                        <div class="flex items-start gap-3 py-2.5 rounded-xl px-3
                            {{ $q['is_right'] ? 'bg-success/6' : 'bg-danger/6' }}">
                            <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5
                                {{ $q['is_right'] ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger' }}">
                                @if ($q['is_right'])
                                    <x-icon name="check" class="w-3.5 h-3.5" />
                                @else
                                    <x-icon name="x" class="w-3.5 h-3.5" />
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-text-muted mb-0.5">Вопрос {{ $q['index'] + 1 }}</p>
                                @if ($q['text'])
                                    <p class="text-sm text-text leading-snug line-clamp-2">{!! strip_tags($q['text']) !!}</p>
                                @endif

                                <div class="flex flex-wrap gap-1.5 mt-1.5">
                                    {{-- User's answers --}}
                                    @foreach ($q['user_answers'] ?? [] as $ans)
                                        <span class="text-xs px-2 py-0.5 rounded-lg font-semibold
                                            {{ $q['is_right'] ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger' }}">
                                            {{ chr(65 + $ans) }}
                                        </span>
                                    @endforeach
                                    @if (empty($q['user_answers']))
                                        <span class="text-xs px-2 py-0.5 rounded-lg bg-gray-100 text-text-muted font-semibold">
                                            Нет ответа
                                        </span>
                                    @endif

                                    {{-- Correct answers (always shown in results) --}}
                                    @if (! $q['is_right'] && ! empty($q['correct']))
                                        <span class="text-xs text-text-muted font-semibold ml-1">→ верно:</span>
                                        @foreach ($q['correct'] as $ans)
                                            <span class="text-xs px-2 py-0.5 rounded-lg bg-success/15 text-success font-semibold">
                                                {{ chr(65 + $ans) }}
                                            </span>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Retry / Back --}}
<div class="flex flex-col sm:flex-row gap-3 fade-up" style="animation-delay: 300ms">
    @php
        $usedAttempts = \App\Models\Test::where('test_access_id', $access->id)
            ->where('user_id', auth()->id())
            ->whereNotNull('completed_at')
            ->count();
        $canRetry = $access->attempts_limit === 0 || $usedAttempts < $access->attempts_limit;
    @endphp

    @if ($canRetry)
        <a href="{{ route('student.test.index', $access) }}"
           class="flex-1 btn text-white text-center font-extrabold py-3 rounded-2xl"
           style="background: var(--color-primary)">
            <x-icon name="refresh" class="w-4 h-4" />
            Пройти ещё раз
        </a>
    @endif

    <a href="{{ route('student.dashboard') }}"
       class="flex-1 btn btn-outline text-center font-semibold py-3 rounded-2xl">
        <x-icon name="home" class="w-4 h-4" />
        В личный кабинет
    </a>
</div>

@endsection
