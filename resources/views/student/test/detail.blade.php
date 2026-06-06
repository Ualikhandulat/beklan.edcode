@extends('layouts.student-test')
@section('title', 'Разбор ответов')

@section('content')

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

            isRight(q) {
                return q.is_right === true;
            },

            isWrong(q) {
                return q.is_right === false;
            },
        }));
    });
</script>

<div x-data="detailApp" class="flex flex-col" style="min-height: calc(100vh - 3.5rem)">

    {{-- ── Top bar ──────────────────────────────────────────────────────────── --}}
    <div class="bg-white border-b border-border sticky top-14 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center gap-4">

            <a href="{{ route('student.test.result', $test) }}"
               class="flex items-center gap-1.5 text-sm font-bold text-text-muted hover:text-text transition-colors shrink-0">
                <x-icon name="arrow-left" class="w-4 h-4" />
                <span class="hidden sm:inline">К результатам</span>
            </a>

            <div class="flex-1 flex items-center justify-center gap-2">
                <span class="text-xs font-extrabold uppercase tracking-widest text-text-muted">Разбор ответов</span>
            </div>

            <div class="shrink-0 flex items-center gap-3">
                @php
                    $wrongCount = collect($subjectsData)->sum(fn($s) => collect($s['questions'])->filter(fn($q) => $q['is_right'] === false)->count());
                    $totalCount = collect($subjectsData)->sum(fn($s) => count($s['questions']));
                    $rightCount = $totalCount - $wrongCount;
                @endphp
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-success/15 text-success">
                    {{ $rightCount }} верно
                </span>
                @if ($wrongCount > 0)
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-danger/15 text-danger">
                        {{ $wrongCount }} ошибок
                    </span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Main area ────────────────────────────────────────────────────────── --}}
    <div class="flex-1 max-w-7xl mx-auto w-full px-0 sm:px-4 lg:px-6 py-0 sm:py-5 flex flex-col lg:flex-row gap-0 sm:gap-4">

        {{-- ── Left sidebar ──────────────────────────────────────────────────── --}}
        <div class="lg:w-72 xl:w-80 shrink-0">

            {{-- Mobile accordion --}}
            <div class="lg:hidden bg-white border-b border-border" x-data="{ open: false }">
                <button @click="open = !open"
                        class="w-full flex items-center justify-between px-4 py-3 text-sm font-bold text-text">
                    <span>
                        Вопрос <span x-text="activeQuestion + 1"></span> из
                        <span x-text="currentSubject?.questions.length"></span>
                        — <span x-text="currentSubject?.subject.title" class="text-primary"></span>
                    </span>
                    <span :class="open ? 'rotate-180' : ''" class="transition-transform duration-200 inline-block">
                        <x-icon name="chevron-down" class="w-4 h-4" />
                    </span>
                </button>
                <div x-show="open" x-transition class="border-t border-border">
                    @include('student.test._sidebar_detail')
                </div>
            </div>

            {{-- Desktop sidebar --}}
            <div class="hidden lg:block bg-white rounded-2xl border border-border overflow-hidden"
                 style="box-shadow: var(--shadow-card); position: sticky; top: 7rem; max-height: calc(100vh - 8rem); overflow-y: auto">
                @include('student.test._sidebar_detail')
            </div>
        </div>

        {{-- ── Right: question ────────────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0">
            <div class="bg-white sm:rounded-2xl border-t sm:border border-border min-h-full"
                 style="box-shadow: var(--shadow-card)">

                <template x-if="currentQuestion">
                    <div class="p-5 sm:p-7">

                        {{-- Question header --}}
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-extrabold text-white shrink-0"
                                  :class="isRight(currentQuestion) ? 'bg-success' : 'bg-danger'">
                                <template x-if="isRight(currentQuestion)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                </template>
                                <template x-if="isWrong(currentQuestion)">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                </template>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-text-muted uppercase tracking-widest truncate"
                                   x-text="currentSubject?.subject.title"></p>
                            </div>
                            <span class="text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-lg border shrink-0"
                                  :class="{
                                      'bg-purple-50 text-purple-600 border-purple-200': currentQuestion.type === 'multi',
                                      'bg-teal-50 text-teal-600 border-teal-200': currentQuestion.type === 'match',
                                      'bg-amber-50 text-amber-700 border-amber-200': currentQuestion.type === 'group',
                                      'bg-blue-50 text-blue-600 border-blue-200': currentQuestion.type === 'one'
                                  }"
                                  x-text="currentQuestion.type === 'multi'
                                      ? 'Несколько ответов (' + currentQuestion.count_answers + ')'
                                      : currentQuestion.type === 'match' ? 'Соответствие'
                                      : currentQuestion.type === 'group' ? 'Контекстный'
                                      : 'Один ответ'">
                            </span>
                        </div>

                        {{-- Question text --}}
                        <div class="text-base font-semibold text-text leading-relaxed mb-7"
                             x-html="currentQuestion.text || ''"></div>

                        {{-- SELECT_ONE / IS_GROUP --}}
                        <template x-if="currentQuestion.type === 'one' || currentQuestion.type === 'group'">
                            <div class="space-y-2">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <div class="w-full flex items-center gap-3 p-3 rounded-2xl border-2"
                                         :class="
                                             (currentQuestion.correct || []).includes(i)
                                                 ? 'border-success bg-success/8'
                                                 : ((currentQuestion.user_answers || []).includes(i) && !(currentQuestion.correct || []).includes(i)
                                                     ? 'border-danger bg-danger/8'
                                                     : 'border-border bg-gray-50/50')
                                         ">
                                        <span class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-xs font-extrabold shrink-0"
                                              :class="
                                                  (currentQuestion.correct || []).includes(i)
                                                      ? 'border-success bg-success text-white'
                                                      : ((currentQuestion.user_answers || []).includes(i)
                                                          ? 'border-danger bg-danger text-white'
                                                          : 'border-border text-text-muted')
                                              "
                                              x-text="String.fromCharCode(65 + i)"></span>
                                        <span class="flex-1 text-sm font-semibold text-gray-900 leading-snug" x-html="v"></span>
                                        <template x-if="(currentQuestion.correct || []).includes(i)">
                                            <svg class="w-4 h-4 text-success shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                        <template x-if="(currentQuestion.user_answers || []).includes(i) && !(currentQuestion.correct || []).includes(i)">
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
                                         :class="
                                             (currentQuestion.correct || []).includes(i)
                                                 ? 'border-success bg-success/8'
                                                 : ((currentQuestion.user_answers || []).includes(i) && !(currentQuestion.correct || []).includes(i)
                                                     ? 'border-danger bg-danger/8'
                                                     : 'border-border bg-gray-50/50')
                                         ">
                                        <span class="w-7 h-7 rounded-lg border-2 flex items-center justify-center shrink-0"
                                              :class="
                                                  (currentQuestion.correct || []).includes(i)
                                                      ? 'border-success bg-success text-white'
                                                      : ((currentQuestion.user_answers || []).includes(i)
                                                          ? 'border-danger bg-danger text-white'
                                                          : 'border-border text-text-muted')
                                              ">
                                            <template x-if="(currentQuestion.correct || []).includes(i)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                            </template>
                                            <template x-if="(currentQuestion.user_answers || []).includes(i) && !(currentQuestion.correct || []).includes(i)">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </template>
                                        </span>
                                        <span class="flex-1 text-sm font-semibold text-gray-900 leading-snug" x-html="v"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        {{-- IS_MATCH --}}
                        <template x-if="currentQuestion.type === 'match'">
                            <div class="space-y-3">

                                {{-- Options reference --}}
                                <div class="rounded-xl bg-gray-50 border border-border p-4">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-text-muted mb-3">Варианты ответов</p>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2.5">
                                        <template x-for="(letter, optIdx) in ['А', 'Б', 'В', 'Г']" :key="optIdx">
                                            <div class="flex items-start gap-2">
                                                <span class="w-5 h-5 rounded-md bg-primary/10 text-primary text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5"
                                                      x-text="letter"></span>
                                                <span class="text-sm font-semibold text-gray-900 leading-snug"
                                                      x-html="currentQuestion.vars[4 + optIdx] || ''"></span>
                                            </div>
                                        </template>
                                    </div>
                                </div>

                                {{-- Pair rows --}}
                                <template x-for="pairIdx in [0, 1]" :key="pairIdx">
                                    <div class="rounded-2xl border-2 p-4"
                                         :class="
                                             (currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                                 ? 'border-success/40 bg-success/4'
                                                 : 'border-danger/40 bg-danger/4'
                                         ">
                                        <div class="flex items-start gap-2.5 mb-3">
                                            <span class="w-6 h-6 rounded-lg bg-gray-100 text-gray-700 text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5"
                                                  x-text="pairIdx + 1"></span>
                                            <div class="text-sm font-bold text-gray-900 leading-snug flex-1"
                                                 x-html="currentQuestion.vars[pairIdx] || ''"></div>
                                        </div>

                                        <div class="flex flex-col sm:flex-row gap-2">
                                            {{-- User answer --}}
                                            <div class="flex-1 rounded-xl p-3 border"
                                                 :class="
                                                     (currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                                         ? 'border-success/40 bg-success/8'
                                                         : 'border-danger/40 bg-danger/8'
                                                 ">
                                                <p class="text-[10px] font-extrabold uppercase tracking-widest mb-1.5"
                                                   :class="
                                                       (currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                                           ? 'text-success'
                                                           : 'text-danger'
                                                   ">Ваш ответ</p>
                                                <template x-if="(currentQuestion.user_answers ?? [])[pairIdx] != null">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-md text-xs font-extrabold flex items-center justify-center shrink-0"
                                                              :class="
                                                                  (currentQuestion.correct || [])[pairIdx] === (currentQuestion.user_answers || [])[pairIdx]
                                                                      ? 'bg-success/20 text-success'
                                                                      : 'bg-danger/20 text-danger'
                                                              "
                                                              x-text="['А','Б','В','Г'][(currentQuestion.user_answers ?? [])[pairIdx]] ?? '?'"></span>
                                                        <span class="text-sm font-semibold text-gray-900 leading-snug"
                                                              x-html="currentQuestion.vars[4 + (currentQuestion.user_answers ?? [])[pairIdx]] || ''"></span>
                                                    </div>
                                                </template>
                                                <template x-if="(currentQuestion.user_answers ?? [])[pairIdx] == null">
                                                    <span class="text-sm text-danger font-semibold">Нет ответа</span>
                                                </template>
                                            </div>

                                            {{-- Correct answer (only if wrong) --}}
                                            <template x-if="(currentQuestion.correct || [])[pairIdx] !== (currentQuestion.user_answers || [])[pairIdx]">
                                                <div class="flex-1 rounded-xl p-3 border border-success/40 bg-success/8">
                                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-success mb-1.5">Верный ответ</p>
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-md bg-success/20 text-success text-xs font-extrabold flex items-center justify-center shrink-0"
                                                              x-text="['А','Б','В','Г'][(currentQuestion.correct || [])[pairIdx]] ?? '?'"></span>
                                                        <span class="text-sm font-semibold text-gray-900 leading-snug"
                                                              x-html="currentQuestion.vars[4 + (currentQuestion.correct || [])[pairIdx]] || ''"></span>
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
                                Назад
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
                                Вперёд
                                <x-icon name="arrow-right" class="w-4 h-4" />
                            </button>
                        </div>

                    </div>
                </template>

            </div>
        </div>

    </div>
</div>


@endsection
