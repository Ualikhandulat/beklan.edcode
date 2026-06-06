@extends('layouts.student')
@section('title', 'Результат теста')

@section('content')

@php
    $pct        = $test->max_score > 0 ? round($test->total_score / $test->max_score * 100) : 0;
    $isGood     = $pct >= 70;
    $isMid      = $pct >= 50 && $pct < 70;
    $scoreColor = $isGood ? 'var(--color-success)' : ($isMid ? 'var(--color-primary)' : 'var(--color-danger)');
    $access     = $test->access;

    $usedAttempts = \App\Models\Test::where('test_access_id', $access->id)
        ->where('user_id', auth()->id())
        ->whereNotNull('completed_at')
        ->count();
    $canRetry = $access->attempts_limit === 0 || $usedAttempts < $access->attempts_limit;

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

@push('head')
<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.4s ease both; }

    @keyframes fillBar {
        from { width: 0%; }
        to   { width: var(--bar-target); }
    }
    .bar-fill {
        animation: fillBar 0.9s cubic-bezier(0.22, 1, 0.36, 1) both;
        animation-delay: 300ms;
    }
</style>
@endpush

{{-- ── Score hero ───────────────────────────────────────────────────────── --}}
<div class="card mb-4 relative overflow-hidden fade-up" style="animation-delay: 0ms">

    <div class="absolute inset-0 pointer-events-none"
         style="background: radial-gradient(ellipse 80% 50% at 50% 0%, color-mix(in srgb, {{ $scoreColor }} 12%, transparent), transparent 70%)"></div>

    <div class="relative text-center pt-2 pb-1">
        <p class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted mb-5">Результат теста</p>

        {{-- Big score --}}
        <div class="flex items-end justify-center gap-2 mb-6">
            <span class="text-6xl sm:text-7xl font-black leading-none tabular-nums"
                  style="color: {{ $scoreColor }}">{{ $test->total_score }}</span>
            <span class="text-xl font-black text-text-muted pb-1.5">/ {{ $test->max_score }}</span>
        </div>

        {{-- Line progress --}}
        <div class="mx-auto max-w-xs mb-2">
            <div class="h-3 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bar-fill"
                     style="--bar-target: {{ $pct }}%; background: {{ $scoreColor }}; width: 0%"></div>
            </div>
            <div class="flex items-center justify-between mt-1.5">
                <span class="text-xs text-text-muted font-semibold">0</span>
                <span class="text-sm font-extrabold tabular-nums" style="color: {{ $scoreColor }}">{{ $pct }}%</span>
                <span class="text-xs text-text-muted font-semibold">{{ $test->max_score }}</span>
            </div>
        </div>

        {{-- Meta --}}
        <div class="flex flex-wrap items-center justify-center gap-4 mt-5 pt-4 border-t border-border text-xs text-text-muted">
            @if ($durationStr !== null)
                <div class="flex items-center gap-1.5">
                    <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
                    {{ $durationStr }}
                </div>
            @endif
            <div class="flex items-center gap-1.5">
                <x-icon name="refresh" class="w-3.5 h-3.5 shrink-0" />
                Попытка {{ $test->attempt_number }}
            </div>
            <div class="flex items-center gap-1.5">
                <x-icon name="calendar" class="w-3.5 h-3.5 shrink-0" />
                {{ $test->completed_at->format('d.m.Y H:i') }}
            </div>
        </div>
    </div>
</div>

{{-- ── Per-subject scores ───────────────────────────────────────────────── --}}
@if (count($subjectsData) > 1)
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
        @foreach ($subjectsData as $i => $subject)
            @php
                $sPct   = $subject['max_score'] > 0 ? round($subject['score'] / $subject['max_score'] * 100) : 0;
                $sGood  = $sPct >= 70;
                $sMid   = $sPct >= 50 && $sPct < 70;
                $sColor = $sGood ? 'var(--color-success)' : ($sMid ? 'var(--color-primary)' : 'var(--color-danger)');
            @endphp
            <div class="card fade-up" style="animation-delay: {{ 120 + $i * 50 }}ms">
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-sm font-extrabold text-text leading-snug truncate">{{ $subject['subject']->title }}</p>
                    <span class="text-sm font-black tabular-nums shrink-0" style="color: {{ $sColor }}">
                        {{ $subject['score'] }}<span class="text-xs text-text-muted font-semibold">/{{ $subject['max_score'] }}</span>
                    </span>
                </div>
                <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bar-fill"
                         style="--bar-target: {{ $sPct }}%; background: {{ $sColor }}; width: 0%; animation-delay: {{ 400 + $i * 80 }}ms"></div>
                </div>
            </div>
        @endforeach
    </div>
@elseif (count($subjectsData) === 1)
    @php
        $s      = $subjectsData[0];
        $sPct   = $s['max_score'] > 0 ? round($s['score'] / $s['max_score'] * 100) : 0;
        $sGood  = $sPct >= 70;
        $sMid   = $sPct >= 50 && $sPct < 70;
        $sColor = $sGood ? 'var(--color-success)' : ($sMid ? 'var(--color-primary)' : 'var(--color-danger)');
    @endphp
    @if ($s['part'])
        <div class="card mb-4 fade-up" style="animation-delay: 120ms">
            <div class="flex items-center justify-between gap-3 mb-3">
                <p class="text-sm font-extrabold text-text leading-snug truncate">{{ $s['subject']->title }}</p>
                <span class="text-sm font-black tabular-nums shrink-0" style="color: {{ $sColor }}">
                    {{ $s['score'] }}<span class="text-xs text-text-muted font-semibold">/{{ $s['max_score'] }}</span>
                </span>
            </div>
            <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full rounded-full bar-fill"
                     style="--bar-target: {{ $sPct }}%; background: {{ $sColor }}; width: 0%; animation-delay: 400ms"></div>
            </div>
        </div>
    @endif
@endif

{{-- ── Actions ──────────────────────────────────────────────────────────── --}}
<div class="flex flex-col sm:flex-row gap-3 fade-up" style="animation-delay: 260ms">

    <a href="{{ route('student.test.detail', $test) }}"
       class="flex-1 btn text-white text-center font-extrabold py-3 rounded-2xl transition-all hover:shadow-lg active:scale-95"
       style="background: var(--color-primary)">
        <x-icon name="book-open" class="w-4 h-4" />
        Разбор ответов
    </a>

    @if ($canRetry)
        <a href="{{ route('student.test.index', $access) }}"
           class="flex-1 btn btn-outline text-center font-semibold py-3 rounded-2xl">
            <x-icon name="refresh" class="w-4 h-4" />
            Пройти ещё раз
        </a>
    @endif

    <a href="{{ route('student.tests.active') }}"
       class="flex-1 btn btn-outline text-center font-semibold py-3 rounded-2xl">
        <x-icon name="home" class="w-4 h-4" />
        В кабинет
    </a>
</div>

@endsection
