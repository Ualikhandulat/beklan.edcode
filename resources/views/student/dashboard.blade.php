@extends('layouts.student')
@section('title', 'Личный кабинет')

@section('content')

<style>
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(14px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .fade-up { animation: fadeUp 0.4s ease both; }
</style>

{{-- ── Profile card ─────────────────────────────────────────────────────── --}}
<div class="card mb-6 fade-up overflow-hidden relative" style="animation-delay: 0ms">

    {{-- Subtle pattern bg --}}
    <div class="absolute inset-0 pointer-events-none"
         style="background: radial-gradient(ellipse 60% 80% at 100% 0%, rgba(242,153,74,0.07) 0%, transparent 70%)"></div>

    <div class="relative flex flex-col sm:flex-row items-start sm:items-center gap-5">

        {{-- Avatar --}}
        <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 text-2xl font-black text-white select-none"
             style="background: linear-gradient(135deg, #F2994A, #e08a3c); box-shadow: 0 8px 20px rgba(242,153,74,0.30)">
            {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
        </div>

        {{-- Info --}}
        <div class="flex-1 min-w-0">
            <p class="text-[10px] font-extrabold text-text-muted uppercase tracking-widest mb-0.5">Личный кабинет</p>
            <h1 class="text-xl sm:text-2xl font-black text-text truncate leading-tight">{{ $user->name }}</h1>
            <div class="flex flex-wrap items-center gap-2 mt-2">
                @if ($user->group)
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-info bg-info-light px-2.5 py-1 rounded-full">
                        <x-icon name="user-group" class="w-3 h-3" />
                        {{ $user->group->title }}
                    </span>
                @endif
                <span class="text-xs text-text-muted font-mono bg-gray-100 px-2 py-0.5 rounded-full">
                    {{ $user->login }}
                </span>
            </div>
        </div>

        {{-- Stats --}}
        @if ($accesses->isNotEmpty())
            @php
                $expiringCount = $accesses->filter(fn($a) =>
                    $a->expires_at && $a->expires_at->diffInDays(now(), false) <= 0 && !$a->expires_at->isPast()
                    || $a->expires_at && $a->expires_at->isFuture() && $a->expires_at->diffInDays(now()) <= 7
                )->count();
            @endphp
            <div class="flex items-center gap-5 sm:gap-6 shrink-0 pt-1 sm:pt-0">
                <div class="text-center">
                    <p class="text-3xl font-black text-primary leading-none">{{ $accesses->count() }}</p>
                    <p class="text-[10px] font-bold text-text-muted uppercase tracking-wide mt-1">тестов</p>
                </div>
                @if ($expiringCount > 0)
                    <div class="text-center">
                        <p class="text-3xl font-black text-danger leading-none">{{ $expiringCount }}</p>
                        <p class="text-[10px] font-bold text-text-muted uppercase tracking-wide mt-1">скоро истекут</p>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>

{{-- ── Tests section ────────────────────────────────────────────────────── --}}
<div class="fade-up" style="animation-delay: 80ms">

    <div class="flex items-center justify-between mb-4">
        <h2 class="text-base font-extrabold text-text">Доступные тесты</h2>
        @if ($accesses->isNotEmpty())
            <span class="text-xs font-bold text-text-muted bg-white border border-border px-2.5 py-1 rounded-full shadow-sm">
                {{ $accesses->count() }}
            </span>
        @endif
    </div>

    {{-- Empty state --}}
    @if ($accesses->isEmpty())
        <div class="card text-center py-16">
            <div class="w-14 h-14 rounded-2xl bg-gray-100 flex items-center justify-center mx-auto mb-4">
                <x-icon name="clipboard-list" class="w-7 h-7 text-text-muted" />
            </div>
            <p class="font-extrabold text-text mb-1">Тестов пока нет</p>
            <p class="text-sm text-text-muted max-w-xs mx-auto leading-relaxed">
                Когда преподаватель выдаст доступ к тесту, он появится здесь.
            </p>
        </div>

    {{-- Test cards --}}
    @else
        <div class="space-y-3">
            @foreach ($accesses as $i => $access)
                @php
                    $isEnt     = $access->type === \App\Enums\TestAccessType::Ent;
                    $color     = $isEnt ? 'var(--color-primary)' : 'var(--color-info)';
                    $colorLight = $isEnt ? 'var(--color-primary-light)' : 'var(--color-info-light)';
                    $subjects  = $access->accessSubjects->map(fn($as) => $as->subject?->title)->filter();
                    $mandatory = $access->accessSubjects->filter(fn($as) => $as->subject?->is_mandatory)->count();
                    $elective  = $access->accessSubjects->filter(fn($as) => !$as->subject?->is_mandatory);
                    $single    = $isEnt ? null : $access->accessSubjects->first();

                    $expiryLabel = null;
                    $expiryClass = 'text-text-muted';
                    if ($access->expires_at) {
                        $diff = (int) now()->diffInDays($access->expires_at, false);
                        if ($diff < 0) {
                            $expiryLabel = 'Истёк';
                            $expiryClass = 'text-danger font-bold';
                        } elseif ($diff === 0) {
                            $expiryLabel = 'Сегодня';
                            $expiryClass = 'text-danger font-bold';
                        } elseif ($diff <= 3) {
                            $expiryLabel = "Осталось {$diff} дн.";
                            $expiryClass = 'text-primary font-semibold';
                        } else {
                            $expiryLabel = $access->expires_at->format('d.m.Y');
                        }
                    }
                @endphp

                <div class="fade-up bg-white rounded-2xl border border-border overflow-hidden flex"
                     style="animation-delay: {{ 120 + $i * 60 }}ms; box-shadow: var(--shadow-card)">

                    {{-- Left accent bar --}}
                    <div class="w-1 shrink-0" style="background: {{ $color }}"></div>

                    {{-- Card body --}}
                    <div class="flex-1 p-5 min-w-0">

                        {{-- Top row: type + title --}}
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="min-w-0">
                                <span class="inline-flex items-center text-[10px] font-extrabold uppercase tracking-widest px-2 py-0.5 rounded-md mb-2"
                                      style="background: {{ $colorLight }}; color: {{ $color }}">
                                    {{ $access->type->label() }}
                                </span>
                                <h3 class="font-extrabold text-text text-base leading-snug">
                                    @if ($isEnt)
                                        Единое национальное тестирование
                                    @else
                                        {{ $single?->subject?->title ?? 'Предмет' }}
                                    @endif
                                </h3>
                            </div>
                        </div>

                        {{-- Subjects --}}
                        @if ($isEnt)
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                {{-- Mandatory --}}
                                @foreach ($access->accessSubjects->filter(fn($as) => $as->subject?->is_mandatory) as $as)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl"
                                          style="background: rgba(242,153,74,0.1); color: #c97b2c">
                                        <x-icon name="shield-check" class="w-3 h-3 shrink-0" />
                                        {{ $as->subject->title }}
                                    </span>
                                @endforeach

                                {{-- Elective --}}
                                @if ($access->student_chooses_subject)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-gray-100 text-text-muted">
                                        <x-icon name="plus" class="w-3 h-3" />
                                        2 предмета на выбор
                                    </span>
                                @else
                                    @foreach ($elective as $as)
                                        @if ($as->subject)
                                            <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-1 rounded-xl bg-info-light text-info">
                                                {{ $as->subject->title }}
                                            </span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        @else
                            {{-- Subject type: part info --}}
                            @if ($single)
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @if (! $single->part_type)
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-gray-100 text-text-muted">
                                            Случайные вопросы
                                        </span>
                                    @elseif ($single->student_chooses_part)
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-info-light text-info">
                                            {{ $single->part_type->label() }} — студент выбирает
                                        </span>
                                    @elseif ($single->part)
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-info-light text-info">
                                            {{ $single->part_type->label() }}: {{ $single->part->title }}
                                        </span>
                                    @else
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-info-light text-info">
                                            {{ $single->part_type->label() }} — случайно
                                        </span>
                                    @endif
                                    @if ($access->question_count > 0)
                                        <span class="text-xs font-semibold px-2.5 py-1 rounded-xl bg-gray-100 text-text-muted">
                                            {{ $access->question_count }} вопр.
                                        </span>
                                    @endif
                                </div>
                            @endif
                        @endif

                        {{-- Meta row + CTA --}}
                        <div class="flex flex-wrap items-center gap-x-4 gap-y-2 pt-3 border-t border-border">

                            {{-- Нұсқа (ENT only) --}}
                            @if ($isEnt)
                                <div class="flex items-center gap-1.5 text-xs text-text-muted">
                                    <x-icon name="clipboard-list" class="w-3.5 h-3.5 shrink-0" />
                                    @if ($access->student_chooses_nusqa)
                                        Нұсқа: студент выбирает
                                    @elseif ($access->nusqa_number)
                                        <span>Нұсқа <span class="font-bold font-mono text-text">{{ $access->nusqa_number }}</span></span>
                                    @else
                                        Нұсқа: случайно
                                    @endif
                                </div>
                            @endif

                            {{-- Attempts --}}
                            <div class="flex items-center gap-1.5 text-xs text-text-muted">
                                <x-icon name="refresh" class="w-3.5 h-3.5 shrink-0" />
                                @if ($access->attempts_limit === 0)
                                    Попыток: ∞
                                @else
                                    Попыток: <span class="font-bold text-text ml-0.5">{{ $access->attempts_limit }}</span>
                                @endif
                            </div>

                            {{-- Expiry --}}
                            <div class="flex items-center gap-1.5 text-xs {{ $expiryClass }}">
                                <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
                                @if ($expiryLabel)
                                    {{ $expiryLabel }}
                                @else
                                    <span class="text-success font-semibold">Бессрочно</span>
                                @endif
                            </div>

                            {{-- CTA --}}
                            <div class="ml-auto">
                                <a href="#"
                                   class="inline-flex items-center gap-1.5 text-xs font-extrabold px-4 py-2 rounded-xl text-white transition-all duration-200 hover:shadow-md active:scale-95"
                                   style="background: {{ $color }}; box-shadow: 0 2px 8px color-mix(in srgb, {{ $color }} 30%, transparent)">
                                    Начать тест
                                    <x-icon name="arrow-right" class="w-3.5 h-3.5" />
                                </a>
                            </div>

                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

</div>

@endsection
