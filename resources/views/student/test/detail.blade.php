@extends('layouts.student-test')
@section('title', __('Разбор ответов'))

@section('content')

@php
    $wrongCount = collect($subjectsData)->sum(fn($s) => collect($s['questions'])->filter(fn($q) => $q['is_right'] === false)->count());
    $totalCount = collect($subjectsData)->sum(fn($s) => count($s['questions']));
    $rightCount  = $totalCount - $wrongCount;
@endphp

@push('head')
<style>
    body { background: #F4F6F9; }
</style>
@endpush

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('detailApp', () => ({
            activeSubject: 0,
            activeQuestion: 0,

            subjects: @json($subjectsData),

            get currentSubject() { return this.subjects[this.activeSubject]; },
            get currentQuestion() { return this.currentSubject?.questions[this.activeQuestion]; },

            goTo(si, qi) {
                this.activeSubject  = si;
                this.activeQuestion = qi;
            },

            isRight(q) { return q.is_right === true; },
            isWrong(q) { return q.is_right === false; },

            // Для разбора multi/one: верный ли это вариант и выбрал ли его ученик (индексы 1-based).
            isCorrect(q, i) { return (q.correct || []).includes(i + 1); },
            isSelected(q, i) { return (q.user_answers || []).includes(i + 1); },

            // Трёхуровневый статус для нумерации и иконок: полностью верный ответ,
            // частично верный (match/multi — набрал часть баллов) или неверный.
            // Старые тесты, оценённые до появления points/max_points, не несут их — для них
            // безопасно откатываемся к бинарному is_right (без «частичного» состояния).
            status(q) {
                if (q.points == null || q.max_points == null) {
                    return q.is_right ? 'full' : 'wrong';
                }
                if (q.points <= 0) return 'wrong';
                if (q.points >= q.max_points) return 'full';
                return 'partial';
            },
            isPartial(q) { return this.status(q) === 'partial'; },

            // Кол-во полностью верных в предмете (частичные считаются отдельно, не как «верно»).
            fullCount(subject) {
                return (subject?.questions ?? []).filter(q => this.status(q) === 'full').length;
            },
        }));
    });
</script>

<div x-data="detailApp" class="flex flex-col" style="min-height: calc(100vh - 3.5rem)">

    {{-- ── Subject — mobile only ─────────────────────────────── --}}
    @if (count($subjectsData) === 1)
    <div class="lg:hidden bg-white border-b border-border px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
            <span class="text-sm font-bold text-text truncate">{{ $subjectsData[0]['subject']->title }}</span>
        </div>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full shrink-0"
              :class="fullCount(subjects[0]) === subjects[0]?.questions.length
                  ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger'"
              x-text="`${fullCount(subjects[0])}/${subjects[0]?.questions.length}`"></span>
    </div>
    @else
    <div class="lg:hidden bg-white border-b border-border" x-data="{ subjectOpen: false }">
        <button type="button"
                @click="subjectOpen = !subjectOpen"
                class="w-full flex items-center justify-between px-4 py-3 cursor-pointer">
            <div class="flex items-center gap-2.5 min-w-0">
                <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                <span class="text-sm font-bold text-text truncate" x-text="currentSubject?.subject.title"></span>
            </div>
            <div class="flex items-center gap-2 shrink-0 ml-3">
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
                      :class="fullCount(subjects[activeSubject]) === subjects[activeSubject]?.questions.length
                          ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger'"
                      x-text="`${fullCount(subjects[activeSubject])}/${subjects[activeSubject]?.questions.length}`"></span>
                <svg class="w-4 h-4 text-text-muted transition-transform duration-200"
                     :class="subjectOpen ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </div>
        </button>
        <div x-show="subjectOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-1"
             @click.outside="subjectOpen = false"
             class="border-t border-border">
            <template x-for="(subject, si) in subjects" :key="si">
                <button type="button"
                        @click="activeSubject = si; activeQuestion = 0; subjectOpen = false"
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-left transition-colors border-b border-border/50 last:border-b-0 cursor-pointer"
                        :class="si === activeSubject ? 'bg-primary/8 text-primary' : 'text-text hover:bg-gray-50'">
                    <span class="w-2 h-2 rounded-full shrink-0"
                          :class="si === activeSubject ? 'bg-primary' : 'bg-border'"></span>
                    <span x-text="subject.subject.title" class="flex-1 truncate"></span>
                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
                          :class="si === activeSubject ? 'bg-primary/15 text-primary'
                              : (fullCount(subject) === subject.questions.length
                                  ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger')"
                          x-text="`${fullCount(subject)}/${subject.questions.length}`"></span>
                </button>
            </template>
        </div>
    </div>
    @endif

    {{-- ── Question pills — mobile horizontal scroll ───────────────────── --}}
    <div class="lg:hidden bg-white border-b border-border overflow-x-auto">
        <div class="flex gap-2 px-4 py-2.5" style="width: max-content; min-width: 100%">
            <template x-for="(q, qi) in currentSubject?.questions ?? []" :key="qi">
                <button type="button"
                        @click="goTo(activeSubject, qi)"
                        class="w-9 h-9 rounded-xl text-xs font-extrabold shrink-0 transition-all duration-150 cursor-pointer"
                        :class="[
                            status(q) === 'full'
                                ? 'bg-success/20 text-success border border-success/30'
                                : (status(q) === 'partial'
                                    ? 'bg-warning/20 text-warning border border-warning/40'
                                    : 'bg-danger/15 text-danger border border-danger/20'),
                            qi === activeQuestion ? 'ring-2 ring-blue-600 ring-offset-2' : ''
                        ]"
                        x-text="qi + 1">
                </button>
            </template>
        </div>
    </div>

    {{-- ── Main area ────────────────────────────────────────────────────── --}}
    <div class="flex-1 max-w-7xl mx-auto w-full px-0 lg:px-6 lg:py-5 flex flex-col lg:flex-row gap-0 lg:gap-4">

        {{-- ── Desktop sidebar: subjects + pills + back ────────────────── --}}
        <div class="hidden lg:flex lg:flex-col gap-3 lg:w-72 xl:w-80 shrink-0"
             style="position: sticky; top: 4.5rem; align-self: start; max-height: calc(100vh - 5.5rem); overflow-y: auto">

            {{-- Subject list --}}
            @if (count($subjectsData) === 1)
            <div class="bg-white rounded-2xl border border-border px-4 py-3 flex items-center gap-3" style="box-shadow: var(--shadow-card)">
                <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                <span class="text-sm font-bold text-text flex-1 truncate">{{ $subjectsData[0]['subject']->title }}</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
                      :class="fullCount(subjects[0]) === subjects[0]?.questions.length
                          ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger'"
                      x-text="`${fullCount(subjects[0])}/${subjects[0]?.questions.length}`"></span>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-border overflow-hidden" style="box-shadow: var(--shadow-card)">
                <template x-for="(subject, si) in subjects" :key="si">
                    <button type="button"
                            @click="activeSubject = si; activeQuestion = 0"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-left transition-colors border-b border-border last:border-b-0 cursor-pointer"
                            :class="si === activeSubject ? 'bg-primary text-white' : 'text-text hover:bg-gray-50'">
                        <span class="w-2 h-2 rounded-full shrink-0"
                              :class="si === activeSubject ? 'bg-white' : 'bg-border'"></span>
                        <span x-text="subject.subject.title" class="flex-1 truncate"></span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
                              :class="si === activeSubject ? 'bg-white/25 text-white'
                                  : (fullCount(subject) === subject.questions.length
                                      ? 'bg-success/15 text-success' : 'bg-danger/15 text-danger')"
                              x-text="`${fullCount(subject)}/${subject.questions.length}`"></span>
                    </button>
                </template>
            </div>
            @endif

            {{-- Question pills --}}
            <div class="bg-white rounded-2xl border border-border p-3" style="box-shadow: var(--shadow-card)">
                <div class="grid gap-1.5" style="grid-template-columns: repeat(auto-fill, minmax(2rem, 1fr))">
                    <template x-for="(q, qi) in currentSubject?.questions ?? []" :key="qi">
                        <button type="button"
                                @click="goTo(activeSubject, qi)"
                                class="aspect-square rounded-lg text-xs font-extrabold transition-all duration-150 cursor-pointer"
                                :class="[
                                    status(q) === 'full'
                                        ? 'bg-success/20 text-success border border-success/30 hover:bg-success/30'
                                        : (status(q) === 'partial'
                                            ? 'bg-warning/20 text-warning border border-warning/40 hover:bg-warning/30'
                                            : 'bg-danger/15 text-danger border border-danger/20 hover:bg-danger/25'),
                                    qi === activeQuestion ? 'ring-2 ring-blue-600 ring-offset-2' : ''
                                ]"
                                x-text="qi + 1">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Back card --}}
            <div class="bg-white rounded-2xl border border-border p-4" style="box-shadow: var(--shadow-card)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-success/15 text-success">
                        {{ __(':count верно', ['count' => $rightCount]) }}
                    </span>
                    @if ($wrongCount > 0)
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-danger/15 text-danger">
                            {{ __(':count ошибок', ['count' => $wrongCount]) }}
                        </span>
                    @endif
                </div>
                <a href="{{ route('student.test.result', $test) }}" class="w-full btn btn-outline">
                    <x-icon name="arrow-left" class="w-4 h-4" />
                    {{ __('К результатам') }}
                </a>
            </div>

        </div>

        {{-- ── Question card ───────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0 pb-20 lg:pb-0">
            <div class="bg-white rounded-none lg:rounded-2xl border-t lg:border border-border min-h-full"
                 style="box-shadow: var(--shadow-card)">

                <template x-if="currentQuestion">
                    <div class="p-5 sm:p-7">

                        {{-- Context passage (IS_GROUP) --}}
                        <template x-if="currentQuestion.context">
                            <div class="text-sm text-text leading-relaxed mb-4"
                                 x-html="currentQuestion.context"></div>
                        </template>

                        {{-- Status icon + question text --}}
                        <div class="flex items-start gap-3 mb-3">
                            <span class="w-7 h-7 rounded-xl flex items-center justify-center text-sm font-extrabold text-white shrink-0 mt-0.5"
                                  :class="status(currentQuestion) === 'full' ? 'bg-success' : (status(currentQuestion) === 'partial' ? 'bg-warning' : 'bg-danger')">
                                <template x-if="status(currentQuestion) === 'full'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </template>
                                <template x-if="status(currentQuestion) === 'partial'">
                                    {{-- Частично верно: показываем заработанные баллы из максимума --}}
                                    <span class="text-[10px] font-black leading-none"
                                          x-text="`${currentQuestion.points}/${currentQuestion.max_points}`"></span>
                                </template>
                                <template x-if="status(currentQuestion) === 'wrong'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </template>
                            </span>
                            <div class="text-base font-semibold text-text leading-relaxed flex-1"
                                 x-html="currentQuestion.text || ''"></div>
                        </div>

                        {{-- Question type --}}
                        <p x-show="currentQuestion.type !== 'group'"
                           class="text-xs font-semibold text-text-muted mb-5"
                           x-text="currentQuestion.type === 'multi'
                               ? @js(__('Выберите :count варианта')).replace(':count', currentQuestion.count_answers)
                               : currentQuestion.type === 'match' ? @js(__('Соответствие'))
                               : @js(__('Один верный ответ'))">
                        </p>

                        {{-- SELECT_ONE / IS_GROUP --}}
                        <template x-if="currentQuestion.type === 'one' || currentQuestion.type === 'group'">
                            <div class="space-y-2">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <div class="w-full flex items-center gap-3 p-3 rounded-2xl border-2"
                                         :class="isCorrect(currentQuestion, i)
                                             ? 'border-success bg-success/8'
                                             : (isSelected(currentQuestion, i)
                                                 ? 'border-danger bg-danger/8'
                                                 : 'border-border bg-gray-50/50')">
                                        <span class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-xs font-extrabold shrink-0"
                                              :class="isCorrect(currentQuestion, i)
                                                  ? 'border-success bg-success text-white'
                                                  : (isSelected(currentQuestion, i)
                                                      ? 'border-danger bg-danger text-white'
                                                      : 'border-border text-text-muted')"
                                              x-text="String.fromCharCode(65 + i)"></span>
                                        <span class="flex-1 text-sm font-semibold text-black leading-snug" x-html="v"></span>
                                        <template x-if="isSelected(currentQuestion, i)">
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 text-white"
                                                  :class="isCorrect(currentQuestion, i) ? 'bg-success' : 'bg-danger'">{{ __('Ваш ответ') }}</span>
                                        </template>
                                        <template x-if="isCorrect(currentQuestion, i) && !isSelected(currentQuestion, i)">
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 bg-success/15 text-success border border-success/30">{{ __('Правильный') }}</span>
                                        </template>
                                        <template x-if="isCorrect(currentQuestion, i)">
                                            <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                        <template x-if="isSelected(currentQuestion, i) && !isCorrect(currentQuestion, i)">
                                            <svg class="w-4 h-4 text-danger shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- SELECT_MULTI --}}
                        <template x-if="currentQuestion.type === 'multi'">
                            <div class="space-y-2">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <div class="w-full flex items-center gap-3 p-3 rounded-2xl border-2"
                                         :class="isCorrect(currentQuestion, i)
                                             ? 'border-success bg-success/8'
                                             : (isSelected(currentQuestion, i)
                                                 ? 'border-danger bg-danger/8'
                                                 : 'border-border bg-gray-50/50')">
                                        <span class="w-7 h-7 rounded-lg border-2 flex items-center justify-center shrink-0"
                                              :class="isCorrect(currentQuestion, i)
                                                  ? 'border-success bg-success text-white'
                                                  : (isSelected(currentQuestion, i)
                                                      ? 'border-danger bg-danger text-white'
                                                      : 'border-border text-text-muted')">
                                            <template x-if="isCorrect(currentQuestion, i)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                            <template x-if="isSelected(currentQuestion, i) && !isCorrect(currentQuestion, i)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </template>
                                        </span>
                                        <span class="flex-1 text-sm font-semibold text-black leading-snug" x-html="v"></span>
                                        <template x-if="isSelected(currentQuestion, i)">
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 text-white"
                                                  :class="isCorrect(currentQuestion, i) ? 'bg-success' : 'bg-danger'">{{ __('Ваш ответ') }}</span>
                                        </template>
                                        <template x-if="isCorrect(currentQuestion, i) && !isSelected(currentQuestion, i)">
                                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 bg-success/15 text-success border border-success/30">{{ __('Правильный') }}</span>
                                        </template>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- IS_MATCH --}}
                        <template x-if="currentQuestion.type === 'match'">
                            <div class="space-y-3">
                                <div class="rounded-xl bg-gray-50 border border-border p-4">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted mb-3">{{ __('Варианты ответов') }}</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2.5">
                                        <template x-for="(letter, optIdx) in ['А', 'Б', 'В', 'Г']" :key="optIdx">
                                            <div class="flex items-start gap-2">
                                                <span class="w-5 h-5 rounded-md bg-primary/10 text-primary text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5"
                                                      x-text="letter"></span>
                                                <span class="text-sm font-semibold text-black leading-snug"
                                                      x-html="currentQuestion.vars[2 + optIdx] || ''"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                                <template x-for="pairIdx in [0, 1]" :key="pairIdx">
                                    <div class="rounded-2xl border-2 p-4"
                                         :class="(currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                             ? 'border-success/40 bg-success/4'
                                             : 'border-danger/40 bg-danger/4'">
                                        <div class="flex items-start gap-2.5 mb-3">
                                            <span class="w-6 h-6 rounded-lg bg-gray-100 text-black text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5"
                                                  x-text="pairIdx + 1"></span>
                                            <div class="text-sm font-bold text-black leading-snug flex-1"
                                                 x-html="currentQuestion.vars[pairIdx] || ''"></div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row gap-2">
                                            <div class="flex-1 rounded-xl p-3 border"
                                                 :class="(currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                                     ? 'border-success/40 bg-success/8'
                                                     : 'border-danger/40 bg-danger/8'">
                                                <p class="text-[10px] font-extrabold uppercase tracking-widest mb-1.5"
                                                   :class="(currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                                       ? 'text-success' : 'text-danger'">{{ __('Ваш ответ') }}</p>
                                                <template x-if="(currentQuestion.user_answers ?? [])[pairIdx] != null">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-md text-xs font-extrabold flex items-center justify-center shrink-0"
                                                              :class="(currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                                                  ? 'bg-success/20 text-success' : 'bg-danger/20 text-danger'"
                                                              x-text="['А','Б','В','Г'][(currentQuestion.user_answers ?? [])[pairIdx] - 3] ?? '?'"></span>
                                                        <span class="text-sm font-semibold text-black leading-snug"
                                                              x-html="currentQuestion.vars[(currentQuestion.user_answers ?? [])[pairIdx] - 1] || ''"></span>
                                                    </div>
                                                </template>
                                                <template x-if="(currentQuestion.user_answers ?? [])[pairIdx] == null">
                                                    <span class="text-sm text-danger font-semibold">{{ __('Нет ответа') }}</span>
                                                </template>
                                            </div>
                                            <template x-if="(currentQuestion.correct || [])[pairIdx] !== (currentQuestion.user_answers || [])[pairIdx]">
                                                <div class="flex-1 rounded-xl p-3 border border-success/40 bg-success/8">
                                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-success mb-1.5">{{ __('Верный ответ') }}</p>
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-md bg-success/20 text-success text-xs font-extrabold flex items-center justify-center shrink-0"
                                                              x-text="['А','Б','В','Г'][(currentQuestion.correct || [])[pairIdx] - 3] ?? '?'"></span>
                                                        <span class="text-sm font-semibold text-black leading-snug"
                                                              x-html="currentQuestion.vars[(currentQuestion.correct || [])[pairIdx] - 1] || ''"></span>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- Navigation --}}
                        <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
                            <button type="button"
                                    @click="activeQuestion > 0 ? activeQuestion-- : (activeSubject > 0 ? (activeSubject--, activeQuestion = subjects[activeSubject].questions.length - 1) : null)"
                                    class="btn btn-outline btn-sm"
                                    :disabled="activeSubject === 0 && activeQuestion === 0">
                                <x-icon name="arrow-left" class="w-4 h-4" />
                                {{ __('Назад') }}
                            </button>
                            <span class="text-xs font-bold text-text-muted">
                                <span x-text="activeQuestion + 1"></span> / <span x-text="currentSubject?.questions.length"></span>
                            </span>
                            <button type="button"
                                    @click="activeQuestion < currentSubject.questions.length - 1
                                        ? activeQuestion++
                                        : (activeSubject < subjects.length - 1 ? (activeSubject++, activeQuestion = 0) : null)"
                                    class="btn btn-sm text-white"
                                    style="background: var(--color-primary)"
                                    :disabled="activeSubject === subjects.length - 1 && activeQuestion === currentSubject.questions.length - 1">
                                {{ __('Вперёд') }}
                                <x-icon name="arrow-right" class="w-4 h-4" />
                            </button>
                        </div>

                    </div>
                </template>

            </div>
        </div>

    </div>

    {{-- ── Mobile sticky bottom bar ────────────────────────────────────── --}}
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-border z-30"
         style="box-shadow: 0 -4px 16px rgba(0,0,0,0.08)">
        <div class="flex items-center gap-3 px-4 py-3">
            <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-success/15 text-success shrink-0">
                {{ __(':count верно', ['count' => $rightCount]) }}
            </span>
            @if ($wrongCount > 0)
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-danger/15 text-danger shrink-0">
                    {{ __(':count ошибок', ['count' => $wrongCount]) }}
                </span>
            @endif
            <a href="{{ route('student.test.result', $test) }}" class="ml-auto btn btn-outline btn-sm">
                <x-icon name="arrow-left" class="w-4 h-4" />
                {{ __('К результатам') }}
            </a>
        </div>
    </div>

</div>

@endsection
