@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-outline btn-sm sm:px-5 sm:py-2.5 sm:text-sm">
        <x-icon name="arrow-left" class="w-4 h-4 shrink-0" />
        К пользователю
    </a>
@endsection

@section('content')

<div class="flex items-center gap-3 mb-5">
    <x-avatar :name="$user->name" size="lg" />
    <div>
        <p class="font-extrabold text-text">{{ $user->name }}</p>
        <p class="text-xs text-text-muted font-mono">{{ $user->login }} @if ($user->group) · {{ $user->group->title }} @endif</p>
    </div>
    @if ($tests->isNotEmpty())
        <span class="ml-auto text-xs font-bold text-text-muted bg-white border border-border px-2.5 py-1 rounded-full shadow-sm">
            {{ $tests->total() }} тестов
        </span>
    @endif
</div>

@if ($tests->isEmpty())
    <div class="card text-center py-16">
        <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
            <x-icon name="chart-bar" class="w-7 h-7 text-text-muted" />
        </div>
        <p class="font-extrabold text-text mb-1">Пока нет результатов</p>
        <p class="text-sm text-text-muted max-w-xs mx-auto leading-relaxed">
            Студент ещё не завершил ни одного теста.
        </p>
    </div>
@else
    <div class="space-y-3">
        @foreach ($tests as $test)
            @php
                $access  = $test->access;
                $pct     = $test->max_score > 0 ? round($test->total_score / $test->max_score * 100) : 0;
                $isGood  = $pct >= 70;
                $isMid   = $pct >= 50 && $pct < 70;
                $color   = $isGood ? 'var(--color-success)' : ($isMid ? 'var(--color-primary)' : 'var(--color-danger)');

                $isEnt  = $access && $access->type === \App\Enums\TestAccessType::Ent;
                $single = $access ? ($isEnt ? null : $access->accessSubjects->first()) : null;
                $title  = $isEnt
                    ? 'ЕНТ'
                    : ($single?->subject?->title ?? ($access?->type->label() ?? 'Тест'));

                $duration = ($test->started_at && $test->completed_at)
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

            <div class="bg-white rounded-2xl border border-border overflow-hidden flex" style="box-shadow: var(--shadow-card)">

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

                            @if ($durationStr !== null)
                                <div class="flex items-center gap-3 mt-3 pt-3 border-t border-border/60">
                                    <span class="text-xs text-text-muted flex items-center gap-1">
                                        <x-icon name="clock" class="w-3 h-3" />
                                        {{ $durationStr }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if ($tests->hasPages())
        <div class="mt-5">
            {{ $tests->links() }}
        </div>
    @endif
@endif

@endsection
