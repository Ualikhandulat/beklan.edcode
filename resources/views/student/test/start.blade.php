@extends('layouts.student')
@section('title', 'Начать тест')

@section('content')

@php
    $isEnt   = $access->type === \App\Enums\TestAccessType::Ent;
    $color   = $isEnt ? 'var(--color-primary)' : 'var(--color-info)';
    $subjects = $access->accessSubjects->map(fn($as) => $as->subject)->filter();
    $mandatory = $access->accessSubjects->filter(fn($as) => $as->subject?->is_mandatory);
    $elective  = $access->accessSubjects->filter(fn($as) => !$as->subject?->is_mandatory);
    $singleCfg = !$isEnt ? $access->accessSubjects->first() : null;
@endphp

<div class="max-w-xl mx-auto"
     x-data="{
         electiveIds: [@foreach(range(0,1) as $i)null,@endforeach],
         nusqaNumber: null,
         partId: null,
     }">

    {{-- Back link --}}
    <a href="{{ route('student.dashboard') }}"
       class="inline-flex items-center gap-1.5 text-sm text-text-muted hover:text-text mb-5 transition-colors">
        <x-icon name="arrow-left" class="w-4 h-4" />
        Назад к кабинету
    </a>

    {{-- Header card --}}
    <div class="bg-white rounded-2xl border border-border overflow-hidden mb-4"
         style="box-shadow: var(--shadow-card)">
        <div class="h-1.5 w-full" style="background: {{ $color }}"></div>
        <div class="p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 text-white font-black text-lg"
                     style="background: {{ $color }}">
                    <x-icon name="clipboard-list" class="w-5 h-5" />
                </div>
                <div>
                    <p class="text-[10px] font-extrabold uppercase tracking-widest mb-0.5"
                       style="color: {{ $color }}">
                        {{ $access->type->label() }}
                    </p>
                    <h1 class="text-lg font-black text-text leading-tight">
                        @if ($isEnt) Единое национальное тестирование
                        @else {{ $singleCfg?->subject?->title ?? 'Тест по предмету' }}
                        @endif
                    </h1>
                </div>
            </div>

            {{-- Meta info --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                @if ($access->duration_minutes)
                    <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2.5">
                        <x-icon name="clock" class="w-4 h-4 text-primary shrink-0" />
                        <div>
                            <p class="text-[10px] text-text-muted font-bold uppercase">Время</p>
                            <p class="text-sm font-extrabold text-text">{{ $access->duration_minutes }} мин.</p>
                        </div>
                    </div>
                @else
                    <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2.5">
                        <x-icon name="clock" class="w-4 h-4 text-success shrink-0" />
                        <div>
                            <p class="text-[10px] text-text-muted font-bold uppercase">Время</p>
                            <p class="text-sm font-extrabold text-success">Без лимита</p>
                        </div>
                    </div>
                @endif

                <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2.5">
                    <x-icon name="refresh" class="w-4 h-4 text-info shrink-0" />
                    <div>
                        <p class="text-[10px] text-text-muted font-bold uppercase">Попыток</p>
                        <p class="text-sm font-extrabold text-text">
                            {{ $access->attempts_limit === 0 ? '∞' : $access->attempts_limit }}
                        </p>
                    </div>
                </div>

                @if ($access->question_count > 0 && !$isEnt)
                    <div class="flex items-center gap-2 bg-gray-50 rounded-xl px-3 py-2.5">
                        <x-icon name="clipboard-list" class="w-4 h-4 text-text-muted shrink-0" />
                        <div>
                            <p class="text-[10px] text-text-muted font-bold uppercase">Вопросов</p>
                            <p class="text-sm font-extrabold text-text">{{ $access->question_count }}</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Warning --}}
    <div class="flex items-start gap-3 p-4 rounded-2xl mb-4"
         style="background: rgba(242,153,74,0.08); border: 1px solid rgba(242,153,74,0.25)">
        <x-icon name="exclamation" class="w-5 h-5 shrink-0 mt-0.5" style="color:#e08a3c" />
        <div class="text-sm text-text">
            <p class="font-bold mb-0.5">Перед началом:</p>
            <ul class="space-y-0.5 text-text-muted">
                <li>• После старта таймер не останавливается</li>
                <li>• Ответы сохраняются автоматически</li>
                @if ($access->duration_minutes)
                    <li>• Тест завершится автоматически через {{ $access->duration_minutes }} минут</li>
                @endif
            </ul>
        </div>
    </div>

    {{-- Subjects preview (ENT mandatory) --}}
    @if ($isEnt && $mandatory->isNotEmpty())
        <div class="card mb-4">
            <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Обязательные предметы</p>
            <div class="flex flex-wrap gap-2">
                @foreach ($mandatory as $as)
                    @if ($as->subject)
                        <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-primary/8 text-primary border border-primary/20">
                            <x-icon name="shield-check" class="w-3 h-3" />
                            {{ $as->subject->title }}
                        </span>
                    @endif
                @endforeach
            </div>
        </div>
    @endif

    {{-- Form --}}
    <form method="POST" action="{{ route('student.test.start', $access) }}">
        @csrf

        {{-- ENT: Student chooses elective subjects --}}
        @if ($isEnt && $access->student_chooses_subject && $electiveSubjects->isNotEmpty())
            <div class="card mb-4">
                <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">
                    Выберите 2 профильных предмета
                </p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach([0, 1] as $i)
                        <x-form.select
                            name="elective_subject_ids[{{ $i }}]"
                            label="Предмет {{ $i + 1 }}"
                            :options="$electiveSubjects->pluck('title', 'id')"
                            placeholder="— Выберите —"
                            :searchable="false"
                            :required="true" />
                    @endforeach
                </div>
            </div>
        @elseif ($isEnt && $elective->isNotEmpty())
            <div class="card mb-4">
                <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Профильные предметы</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($elective as $as)
                        @if ($as->subject)
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-3 py-1.5 rounded-xl bg-info-light text-info">
                                {{ $as->subject->title }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ENT: Student chooses нұсқа --}}
        @if ($isEnt && $access->student_chooses_nusqa && $nusqaNumbers->isNotEmpty())
            <div class="card mb-4">
                <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Выберите нұсқа</p>
                <div class="flex flex-wrap gap-2">
                    @foreach ($nusqaNumbers as $n)
                        <label class="cursor-pointer">
                            <input type="radio" name="nusqa_number" value="{{ $n['number'] }}" class="sr-only peer" required>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl border border-border text-sm font-bold text-text-muted transition-all peer-checked:border-primary peer-checked:text-primary peer-checked:bg-primary/8 hover:border-primary/50">
                                {{ $n['title'] }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Subject: Student chooses part --}}
        @if (!$isEnt && $singleCfg?->student_chooses_part && $choosableParts->isNotEmpty())
            <div class="card mb-4">
                <x-form.select
                    name="part_id"
                    label="Выберите {{ $singleCfg->part_type?->label() ?? 'раздел' }}"
                    :options="$choosableParts->pluck('title', 'id')"
                    placeholder="— Выберите —"
                    :searchable="false"
                    :required="true" />
            </div>
        @endif

        <button type="submit"
                class="w-full btn text-white font-extrabold py-3.5 text-base rounded-2xl transition-all duration-200 hover:shadow-lg active:scale-[0.99]"
                style="background: {{ $color }}">
            <x-icon name="play" class="w-5 h-5" />
            Начать тестирование
        </button>
    </form>

</div>

@endsection
