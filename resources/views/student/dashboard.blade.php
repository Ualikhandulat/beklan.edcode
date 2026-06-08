@extends('layouts.student')
@section('title', __('Личный кабинет'))

@section('content')

@php
    use App\Enums\TestAccessType;
@endphp

<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.4s ease both; }
</style>

{{-- Profile mini-header --}}
<div class="flex items-center gap-3 mb-6 fade-up">
    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-base font-black text-white select-none"
         style="background: linear-gradient(135deg, #F2994A, #e08a3c)">
        {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
    </div>
    <div class="min-w-0">
        <p class="text-sm font-extrabold text-text truncate leading-tight">{{ $user->name }}</p>
        @if ($user->group)
            <p class="text-xs text-text-muted">{{ $user->group->title }}</p>
        @endif
    </div>
</div>

@if ($inProgress->isEmpty() && $available->isEmpty())
    <div class="card text-center py-16 fade-up mb-10" style="animation-delay: 80ms">
        <div class="w-14 h-14 rounded-2xl bg-success/10 flex items-center justify-center mx-auto mb-4">
            <x-icon name="check" class="w-7 h-7 text-success" />
        </div>
        <p class="font-extrabold text-text mb-1">{{ __('Все тесты пройдены') }}</p>
        <p class="text-sm text-text-muted max-w-xs mx-auto leading-relaxed">
            {{ __('Нет доступных или активных тестов.') }}
        </p>
    </div>
@else

    {{-- ── В процессе ── --}}
    @if ($inProgress->isNotEmpty())
        <div class="flex items-center justify-between mb-3 fade-up" style="animation-delay: 40ms">
            <h2 class="text-base font-extrabold text-text">{{ __('В процессе') }}</h2>
            <span class="text-xs font-bold text-text-muted bg-white border border-border px-2.5 py-1 rounded-full shadow-sm">
                {{ $inProgress->count() }}
            </span>
        </div>

        <div class="space-y-3 mb-10">
            @foreach ($inProgress as $i => $access)
                @php
                    $test       = $inProgressTests->get($access->id);
                    $isEnt      = $access->type === TestAccessType::Ent;
                    $color      = $isEnt ? 'var(--color-primary)' : 'var(--color-info)';
                    $colorLight = $isEnt ? 'var(--color-primary-light)' : 'var(--color-info-light)';
                    $single     = $isEnt ? null : $access->accessSubjects->first();

                    $expiryLabel = null;
                    $expiryClass = 'text-text-muted';
                    if ($access->expires_at) {
                        $diff = (int) now()->diffInDays($access->expires_at, false);
                        if ($diff < 0)       { $expiryLabel = __('Доступ истёк');       $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff === 0) { $expiryLabel = __('Сегодня');            $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff <= 3)  { $expiryLabel = __('Осталось :count дн.', ['count' => $diff]); $expiryClass = 'text-primary font-semibold'; }
                        else                 { $expiryLabel = $access->expires_at->format('d.m.Y'); }
                    }
                @endphp

                <div class="fade-up bg-white rounded-2xl border-2 overflow-hidden flex"
                     style="animation-delay: {{ 80 + $i * 60 }}ms; border-color: {{ $color }}; box-shadow: 0 4px 16px color-mix(in srgb, {{ $color }} 18%, transparent)">

                    <div class="w-1.5 shrink-0" style="background: {{ $color }}"></div>

                    <div class="flex-1 p-5 min-w-0">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded-md"
                                          style="background: {{ $colorLight }}; color: {{ $color }}">
                                        {{ $access->type->label() }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded-md bg-amber-50 text-amber-600 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        {{ __('В процессе') }}
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-text text-base leading-snug">
                                    @if ($isEnt)
                                        {{ __('Единое национальное тестирование') }}
                                    @else
                                        {{ $single?->subject?->title ?? __('Предмет') }}
                                    @endif
                                </h3>
                            </div>
                        </div>

                        @if ($isEnt)
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                @foreach ($access->accessSubjects->filter(fn($as) => $as->subject?->is_mandatory) as $as)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl"
                                          style="background: rgba(242,153,74,0.1); color: #c97b2c">
                                        <x-icon name="shield-check" class="w-3 h-3 shrink-0" />
                                        {{ $as->subject->title }}
                                    </span>
                                @endforeach
                                @if ($access->student_chooses_subject)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-gray-100 text-text-muted">
                                        <x-icon name="plus" class="w-3 h-3" />
                                        {{ __('2 предмета на выбор') }}
                                    </span>
                                @else
                                    @foreach ($access->accessSubjects->filter(fn($as) => !$as->subject?->is_mandatory) as $as)
                                        @if ($as->subject)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-info-light text-info">
                                                {{ $as->subject->title }}
                                            </span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pt-3 border-t border-border">

                            @if ($expiryLabel)
                                <div class="flex items-center gap-1.5 text-xs {{ $expiryClass }}">
                                    <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
                                    {{ $expiryLabel }}
                                </div>
                            @endif

                            <div class="ml-auto">
                                <a href="{{ route('student.test.process', $test) }}"
                                   class="btn inline-flex items-center gap-1.5 text-xs font-extrabold px-4 py-2 rounded-xl text-white transition-all duration-200 hover:shadow-md"
                                   style="background: {{ $color }}">
                                    {{ __('Продолжить') }}
                                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- ── Доступные тесты ── --}}
    @if ($available->isNotEmpty())
        <div class="flex items-center justify-between mb-3 fade-up" style="animation-delay: 40ms">
            <h2 class="text-base font-extrabold text-text">{{ __('Доступные тесты') }}</h2>
            <span class="text-xs font-bold text-text-muted bg-white border border-border px-2.5 py-1 rounded-full shadow-sm">
                {{ $available->count() }}
            </span>
        </div>

        @if ($hasActiveTest)
            <div class="alert alert-warning mb-4 fade-up" style="animation-delay: 60ms">
                <x-icon name="information-circle" class="w-4 h-4 shrink-0 mt-0.5" />
                <span>{{ __('Завершите активный тест, чтобы начать новый.') }}</span>
            </div>
        @endif

        <div class="space-y-3 mb-10">
            @foreach ($available as $i => $access)
                @php
                    $isEnt      = $access->type === TestAccessType::Ent;
                    $color      = $isEnt ? 'var(--color-primary)' : 'var(--color-info)';
                    $colorLight = $isEnt ? 'var(--color-primary-light)' : 'var(--color-info-light)';
                    $single     = $isEnt ? null : $access->accessSubjects->first();
                    $completedCount = $completedCounts->get($access->id, 0);

                    $expiryLabel = null;
                    $expiryClass = 'text-text-muted';
                    if ($access->expires_at) {
                        $diff = (int) now()->diffInDays($access->expires_at, false);
                        if ($diff < 0)       { $expiryLabel = __('Доступ истёк');       $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff === 0) { $expiryLabel = __('Сегодня');            $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff <= 3)  { $expiryLabel = __('Осталось :count дн.', ['count' => $diff]); $expiryClass = 'text-primary font-semibold'; }
                        else                 { $expiryLabel = $access->expires_at->format('d.m.Y'); }
                    }
                @endphp

                <div class="fade-up bg-white rounded-2xl border border-border overflow-hidden flex {{ $hasActiveTest ? 'opacity-60' : '' }}"
                     style="animation-delay: {{ 100 + $i * 60 }}ms; box-shadow: var(--shadow-card)">

                    <div class="w-1 shrink-0" style="background: {{ $color }}"></div>

                    <div class="flex-1 p-5 min-w-0">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="inline-flex items-center text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded-md"
                                          style="background: {{ $colorLight }}; color: {{ $color }}">
                                        {{ $access->type->label() }}
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-text text-base leading-snug">
                                    @if ($isEnt)
                                        {{ __('Единое национальное тестирование') }}
                                    @else
                                        {{ $single?->subject?->title ?? __('Предмет') }}
                                    @endif
                                </h3>
                            </div>
                        </div>

                        @if ($isEnt)
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                @foreach ($access->accessSubjects->filter(fn($as) => $as->subject?->is_mandatory) as $as)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl"
                                          style="background: rgba(242,153,74,0.1); color: #c97b2c">
                                        <x-icon name="shield-check" class="w-3 h-3 shrink-0" />
                                        {{ $as->subject->title }}
                                    </span>
                                @endforeach
                                @if ($access->student_chooses_subject)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-gray-100 text-text-muted">
                                        <x-icon name="plus" class="w-3 h-3" />
                                        {{ __('2 предмета на выбор') }}
                                    </span>
                                @else
                                    @foreach ($access->accessSubjects->filter(fn($as) => !$as->subject?->is_mandatory) as $as)
                                        @if ($as->subject)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-info-light text-info">
                                                {{ $as->subject->title }}
                                            </span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @endif

                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pt-3 border-t border-border">

                            @if ($access->attempts_limit > 0)
                                <div class="flex items-center gap-1.5 text-xs text-text-muted">
                                    <x-icon name="refresh" class="w-3.5 h-3.5 shrink-0" />
                                    <span>{{ __('Осталось попыток') }}: <span class="font-bold text-text">{{ $access->attempts_limit - $completedCount }}</span></span>
                                </div>
                            @endif

                            @if ($expiryLabel)
                                <div class="flex items-center gap-1.5 text-xs {{ $expiryClass }}">
                                    <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
                                    {{ $expiryLabel }}
                                </div>
                            @endif

                            <div class="ml-auto">
                                @if ($hasActiveTest)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-extrabold px-4 py-2 rounded-xl text-white cursor-not-allowed"
                                          style="background: {{ $color }}; opacity: 0.5">
                                        {{ __('Начать тест') }}
                                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                                    </span>
                                @else
                                    <a href="{{ route('student.test.index', $access) }}"
                                       class="btn inline-flex items-center gap-1.5 text-xs font-extrabold px-4 py-2 rounded-xl text-white transition-all duration-200 hover:shadow-md"
                                       style="background: {{ $color }}">
                                        {{ __('Начать тест') }}
                                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

@endif

{{-- ── Прогресс и активность ────────────────────────────────────────────── --}}
@if ($stats['totalTests'] > 0)
    @php
        $avgGood  = $stats['avgPct'] >= 70;
        $avgMid   = $stats['avgPct'] >= 50 && $stats['avgPct'] < 70;
        $avgColor = $avgGood ? 'var(--color-success)' : ($avgMid ? 'var(--color-primary)' : 'var(--color-danger)');

        $chartW = 100;
        $chartH = 40;
        $chartPad = 4;
        $progressPoints = $stats['progress'];
        $pCount = $progressPoints->count();
        $progressCoords = $progressPoints->values()->map(function ($p, $i) use ($pCount, $chartW, $chartH, $chartPad) {
            $x = $pCount > 1 ? round($i * ($chartW / ($pCount - 1)), 2) : $chartW / 2;
            $y = round($chartH - $chartPad - ($p['pct'] / 100) * ($chartH - $chartPad * 2), 2);

            return ['x' => $x, 'y' => $y, 'pct' => $p['pct'], 'date' => $p['date']];
        });
        $progressPolyline = $progressCoords->map(fn ($c) => "{$c['x']},{$c['y']}")->implode(' ');
        $progressArea = $pCount > 0
            ? "0,{$chartH} {$progressPolyline} {$chartW},{$chartH}"
            : '';

        $maxActivity = max(1, $stats['activity']->max('count'));
    @endphp

    <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-3 fade-up" style="animation-delay: 20ms">
        <div class="card text-center py-4 px-2">
            <p class="text-2xl font-black text-text tabular-nums leading-none">{{ $stats['totalTests'] }}</p>
            <p class="text-[10px] sm:text-[11px] text-text-muted font-bold uppercase tracking-wide mt-1.5">{{ __('Тестов пройдено') }}</p>
        </div>
        <div class="card text-center py-4 px-2">
            <p class="text-2xl font-black tabular-nums leading-none" style="color: {{ $avgColor }}">{{ $stats['avgPct'] }}%</p>
            <p class="text-[10px] sm:text-[11px] text-text-muted font-bold uppercase tracking-wide mt-1.5">{{ __('Средний балл') }}</p>
        </div>
        <div class="card text-center py-4 px-2">
            <p class="text-2xl font-black tabular-nums leading-none" style="color: var(--color-success)">{{ $stats['bestPct'] }}%</p>
            <p class="text-[10px] sm:text-[11px] text-text-muted font-bold uppercase tracking-wide mt-1.5">{{ __('Лучший результат') }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 mb-3">

        {{-- Progress trend --}}
        @if ($pCount > 1)
            <div class="card fade-up" style="animation-delay: 60ms">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-sm font-extrabold text-text">{{ __('Прогресс по тестам') }}</p>
                    <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded-md bg-info-light text-info">
                        <x-icon name="chart-bar" class="w-3 h-3" />
                        {{ __('Последние :count', ['count' => $pCount]) }}
                    </span>
                </div>

                <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" preserveAspectRatio="none" class="w-full h-28 sm:h-32 overflow-visible">
                    <polygon points="{{ $progressArea }}" fill="var(--color-primary)" opacity="0.10"></polygon>
                    <polyline points="{{ $progressPolyline }}" fill="none" stroke="var(--color-primary)"
                              stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"
                              vector-effect="non-scaling-stroke"></polyline>
                    @foreach ($progressCoords as $c)
                        <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="1.7" fill="white"
                                stroke="var(--color-primary)" stroke-width="1.3" vector-effect="non-scaling-stroke">
                            <title>{{ $c['date'] }}: {{ $c['pct'] }}%</title>
                        </circle>
                    @endforeach
                </svg>

                <div class="flex items-center justify-between mt-2 text-[10px] text-text-muted font-semibold">
                    <span>{{ $progressCoords->first()['date'] }} · {{ $progressCoords->first()['pct'] }}%</span>
                    <span>{{ $progressCoords->last()['date'] }} · {{ $progressCoords->last()['pct'] }}%</span>
                </div>
            </div>
        @endif

        {{-- Subject breakdown --}}
        @if ($stats['subjects']->isNotEmpty())
            <div class="card fade-up" style="animation-delay: 100ms">
                <p class="text-sm font-extrabold text-text mb-3">{{ __('Сильные и слабые стороны') }}</p>

                @foreach ($stats['subjects'] as $subject)
                    @php
                        $spct  = (int) $subject['pct'];
                        $sgood = $spct >= 70;
                        $smid  = $spct >= 50 && $spct < 70;
                        $scol  = $sgood ? 'var(--color-success)' : ($smid ? 'var(--color-primary)' : 'var(--color-danger)');
                    @endphp
                    <div class="mb-3 last:mb-0">
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="text-xs font-bold text-text truncate">{{ $subject['title'] }}</span>
                            <span class="text-xs font-extrabold tabular-nums shrink-0" style="color: {{ $scol }}">{{ $spct }}%</span>
                        </div>
                        <div class="h-2 w-full bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="width: {{ $spct }}%; background: {{ $scol }}"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Activity strip --}}
    <div class="card mb-6 fade-up" style="animation-delay: 140ms">
        <div class="flex items-center justify-between mb-3">
            <p class="text-sm font-extrabold text-text">{{ __('Активность за 14 дней') }}</p>
            <span class="inline-flex items-center gap-1.5 text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded-md bg-success-light text-success">
                <x-icon name="refresh" class="w-3 h-3" />
                {{ __(':count за период', ['count' => $stats['activity']->sum('count')]) }}
            </span>
        </div>

        <div class="flex items-end justify-between gap-1 sm:gap-1.5 h-20">
            @foreach ($stats['activity'] as $day)
                @php
                    $active = $day['count'] > 0;
                    $barH = $active ? max(18, (int) round(($day['count'] / $maxActivity) * 100)) : 6;
                @endphp
                <div class="flex-1 h-full flex items-end" title="{{ $day['date'] }}: {{ $day['count'] }}">
                    <div class="w-full rounded-md transition-all duration-300 {{ $active ? '' : 'bg-gray-200' }}"
                         style="height: {{ $barH }}%; {{ $active ? 'background: var(--color-primary)' : '' }}"></div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between mt-2 text-[10px] text-text-muted font-semibold">
            <span>{{ $stats['activity']->first()['date'] }}</span>
            <span>{{ $stats['activity']->last()['date'] }}</span>
        </div>
    </div>
@endif


@endsection
