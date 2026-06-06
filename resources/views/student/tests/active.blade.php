@extends('layouts.student')
@section('title', 'Активные тесты')

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
    <div class="card text-center py-16 fade-up" style="animation-delay: 80ms">
        <div class="w-14 h-14 rounded-2xl bg-success/10 flex items-center justify-center mx-auto mb-4">
            <x-icon name="check" class="w-7 h-7 text-success" />
        </div>
        <p class="font-extrabold text-text mb-1">Все тесты пройдены</p>
        <p class="text-sm text-text-muted max-w-xs mx-auto leading-relaxed">
            Нет доступных или активных тестов.
        </p>
    </div>
@else

    {{-- ── В процессе ── --}}
    @if ($inProgress->isNotEmpty())
        <div class="flex items-center justify-between mb-3 fade-up" style="animation-delay: 40ms">
            <h2 class="text-base font-extrabold text-text">В процессе</h2>
            <span class="text-xs font-bold text-text-muted bg-white border border-border px-2.5 py-1 rounded-full shadow-sm">
                {{ $inProgress->count() }}
            </span>
        </div>

        <div class="space-y-3 mb-6">
            @foreach ($inProgress as $i => $access)
                @php
                    $test       = $inProgressTests->get($access->id);
                    $isEnt      = $access->type === TestAccessType::Ent;
                    $color      = $isEnt ? 'var(--color-primary)' : 'var(--color-info)';
                    $colorLight = $isEnt ? 'var(--color-primary-light)' : 'var(--color-info-light)';
                    $single     = $isEnt ? null : $access->accessSubjects->first();

                    $completedCount = \App\Models\Test::where('test_access_id', $access->id)
                        ->where('user_id', auth()->id())
                        ->whereNotNull('completed_at')
                        ->count();

                    $expiryLabel = null;
                    $expiryClass = 'text-text-muted';
                    if ($access->expires_at) {
                        $diff = (int) now()->diffInDays($access->expires_at, false);
                        if ($diff < 0)       { $expiryLabel = 'Доступ истёк';       $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff === 0) { $expiryLabel = 'Сегодня';            $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff <= 3)  { $expiryLabel = "Осталось {$diff} дн."; $expiryClass = 'text-primary font-semibold'; }
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
                                        В процессе
                                    </span>
                                </div>
                                <h3 class="font-extrabold text-text text-base leading-snug">
                                    @if ($isEnt)
                                        Единое национальное тестирование
                                    @else
                                        {{ $single?->subject?->title ?? 'Предмет' }}
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
                                        2 предмета на выбор
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
                                    <span>Попытка <span class="font-bold text-text">{{ $completedCount + 1 }}/{{ $access->attempts_limit }}</span></span>
                                </div>
                            @endif

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
                                    Продолжить
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
            <h2 class="text-base font-extrabold text-text">Доступные тесты</h2>
            <span class="text-xs font-bold text-text-muted bg-white border border-border px-2.5 py-1 rounded-full shadow-sm">
                {{ $available->count() }}
            </span>
        </div>

        @if ($hasActiveTest)
            <div class="alert alert-warning mb-4 fade-up" style="animation-delay: 60ms">
                <x-icon name="information-circle" class="w-4 h-4 shrink-0 mt-0.5" />
                <span>Завершите текущий тест, чтобы начать новый.</span>
            </div>
        @endif

        <div class="space-y-3">
            @foreach ($available as $i => $access)
                @php
                    $isEnt      = $access->type === TestAccessType::Ent;
                    $color      = $isEnt ? 'var(--color-primary)' : 'var(--color-info)';
                    $colorLight = $isEnt ? 'var(--color-primary-light)' : 'var(--color-info-light)';
                    $single     = $isEnt ? null : $access->accessSubjects->first();

                    $completedCount = \App\Models\Test::where('test_access_id', $access->id)
                        ->where('user_id', auth()->id())
                        ->whereNotNull('completed_at')
                        ->count();

                    $expiryLabel = null;
                    $expiryClass = 'text-text-muted';
                    if ($access->expires_at) {
                        $diff = (int) now()->diffInDays($access->expires_at, false);
                        if ($diff < 0)       { $expiryLabel = 'Доступ истёк';         $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff === 0) { $expiryLabel = 'Сегодня';              $expiryClass = 'text-danger font-bold'; }
                        elseif ($diff <= 3)  { $expiryLabel = "Осталось {$diff} дн."; $expiryClass = 'text-primary font-semibold'; }
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
                                        Единое национальное тестирование
                                    @else
                                        {{ $single?->subject?->title ?? 'Предмет' }}
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
                                        2 предмета на выбор
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
                                    <span>Попыток: <span class="font-bold text-text">{{ $completedCount }}/{{ $access->attempts_limit }}</span></span>
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
                                        Начать тест
                                        <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                                    </span>
                                @else
                                    <a href="{{ route('student.test.index', $access) }}"
                                       class="btn inline-flex items-center gap-1.5 text-xs font-extrabold px-4 py-2 rounded-xl text-white transition-all duration-200 hover:shadow-md"
                                       style="background: {{ $color }}">
                                        Начать тест
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

@endsection
