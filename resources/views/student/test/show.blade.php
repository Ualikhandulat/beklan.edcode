@extends('layouts.student')
@section('title', 'Тестирование')

@section('content')

@php
    $access         = $test->access;
    $secondsLeft    = $test->secondsRemaining();
    $totalQuestions = collect($subjectsData)->sum(fn($s) => count($s['questions']));
    $answered       = collect($subjectsData)->sum('answered');
@endphp

@push('head')
<style>
    body { background: #F4F6F9; }
</style>
@endpush

{{-- Alpine component defined in a <script> tag to avoid HTML attribute parsing issues with > < characters --}}
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testApp', () => ({
            activeSubject: 0,
            activeQuestion: 0,
            finishing: false,
            confirmFinish: false,
            totalAnswered: {{ $answered }},
            totalQuestions: {{ $totalQuestions }},
            secondsLeft: {{ $secondsLeft ?? 'null' }},

            get timerStr() {
                if (this.secondsLeft === null) return null;
                const h = Math.floor(this.secondsLeft / 3600);
                const m = Math.floor((this.secondsLeft % 3600) / 60);
                const s = this.secondsLeft % 60;
                if (h > 0) return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
                return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            },

            get timerUrgent() {
                return this.secondsLeft !== null && this.secondsLeft <= 300;
            },

            startTimer() {
                if (this.secondsLeft === null) return;
                const tick = () => {
                    if (this.secondsLeft <= 0) { this.autoFinish(); return; }
                    this.secondsLeft--;
                    setTimeout(tick, 1000);
                };
                setTimeout(tick, 1000);
            },

            autoFinish() {
                if (this.finishing) return;
                this.finishing = true;
                fetch('{{ route('student.test.finish', $test) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(d => { window.location = d.redirect; });
            },

            saveAnswer(subjectIdx, detailId, answers) {
                const testSubjectId = this.subjects[subjectIdx].test_subject_id;
                fetch('{{ route('student.test.answer', $test) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ test_subject_id: testSubjectId, detail_id: detailId, user_answers: answers }),
                });
            },

            subjects: @json($subjectsData),

            get currentSubject() { return this.subjects[this.activeSubject]; },
            get currentQuestion() { return this.currentSubject?.questions[this.activeQuestion]; },

            goTo(si, qi) {
                this.activeSubject  = si;
                this.activeQuestion = qi;
            },

            answerQuestion(answer) {
                const q = this.currentQuestion;
                if (!q) return;
                const type = q.type;
                let answers;

                if (type === 'one' || type === 'group') {
                    answers = [answer];
                } else if (type === 'multi') {
                    answers = [...(q.user_answers || [])];
                    const idx = answers.indexOf(answer);
                    if (idx >= 0) { answers.splice(idx, 1); } else { answers.push(answer); }
                    if (answers.length > q.count_answers) { answers.shift(); }
                } else if (type === 'match') {
                    answers = [...(q.user_answers || [null, null])];
                    while (answers.length < 2) answers.push(null);
                    answers[answer.pair] = answer.val;
                }

                const wasEmpty = !q.user_answers || q.user_answers.length === 0;
                q.user_answers = answers;
                this.subjects[this.activeSubject].questions[this.activeQuestion].user_answers = answers;

                const isNowAnswered = answers && answers.length > 0 && answers.every(a => a !== null);
                if (wasEmpty && isNowAnswered) {
                    this.subjects[this.activeSubject].answered++;
                    this.totalAnswered++;
                } else if (!wasEmpty && !isNowAnswered) {
                    this.subjects[this.activeSubject].answered--;
                    this.totalAnswered--;
                }

                this.saveAnswer(this.activeSubject, q.detail_id, answers.filter(a => a !== null));
            },

            isAnswered(q) {
                return q.user_answers && q.user_answers.length > 0 && q.user_answers.every(a => a !== null);
            },
        }));
    });
</script>

<div
    x-data="testApp"
    x-init="startTimer()"
    class="flex flex-col"
    style="min-height: calc(100vh - 3.5rem)"
>

    {{-- ── Top bar: timer + progress ────────────────────────────────────── --}}
    <div class="bg-white border-b border-border sticky top-14 z-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center gap-4">

            {{-- Left: progress --}}
            <div class="flex items-center gap-2 min-w-0 flex-1">
                <div class="hidden sm:block w-28 h-2 bg-gray-100 rounded-full overflow-hidden shrink-0">
                    <div class="h-full bg-primary rounded-full transition-all duration-500"
                         :style="`width: ${totalQuestions > 0 ? Math.round(totalAnswered/totalQuestions*100) : 0}%`"></div>
                </div>
                <span class="text-xs font-bold text-text-muted shrink-0">
                    <span x-text="totalAnswered" class="text-text font-extrabold"></span>/<span x-text="totalQuestions"></span> отв.
                </span>
            </div>

            {{-- Center: timer --}}
            <div class="flex items-center gap-2 shrink-0">
                <template x-if="timerStr !== null">
                    <div class="flex items-center gap-1.5 px-4 py-1.5 rounded-full border transition-all duration-300"
                         :class="timerUrgent ? 'border-danger/40 bg-danger/8 text-danger' : 'border-border bg-gray-50 text-text'">
                        <x-icon name="clock" class="w-3.5 h-3.5 shrink-0" />
                        <span class="font-mono text-sm font-extrabold tabular-nums" x-text="timerStr"></span>
                    </div>
                </template>
                <template x-if="timerStr === null">
                    <span class="text-xs text-text-muted font-semibold px-3 py-1.5 bg-gray-50 rounded-full border border-border">
                        Без лимита
                    </span>
                </template>
            </div>

            {{-- Right: finish --}}
            <div class="flex-1 flex justify-end">
                <button @click="confirmFinish = true" class="btn btn-danger btn-sm">
                    <x-icon name="check" class="w-4 h-4" />
                    <span class="hidden sm:inline">Завершить тест</span>
                    <span class="sm:hidden">Завершить</span>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Main area ────────────────────────────────────────────────────── --}}
    <div class="flex-1 max-w-7xl mx-auto w-full px-0 sm:px-4 lg:px-6 py-0 sm:py-5 flex flex-col lg:flex-row gap-0 sm:gap-4">

        {{-- ── Left sidebar ──────────────────────────────────────────────── --}}
        <div class="lg:w-72 xl:w-80 shrink-0">

            {{-- Mobile: accordion --}}
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
                    @include('student.test._sidebar')
                </div>
            </div>

            {{-- Desktop: sticky --}}
            <div class="hidden lg:block bg-white rounded-2xl border border-border overflow-hidden"
                 style="box-shadow: var(--shadow-card); position: sticky; top: 7rem; max-height: calc(100vh - 8rem); overflow-y: auto">
                @include('student.test._sidebar')
            </div>
        </div>

        {{-- ── Right: question ────────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0">
            <div class="bg-white sm:rounded-2xl border-t sm:border border-border min-h-full"
                 style="box-shadow: var(--shadow-card)">

                <template x-if="currentQuestion">
                    <div class="p-5 sm:p-7">

                        {{-- Question header --}}
                        <div class="flex items-center gap-3 mb-6">
                            <span class="w-8 h-8 rounded-xl flex items-center justify-center text-sm font-extrabold text-white shrink-0"
                                  style="background: var(--color-primary)">
                                <span x-text="activeQuestion + 1"></span>
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-bold text-text-muted uppercase tracking-widest truncate"
                                   x-text="currentSubject?.subject.title"></p>
                            </div>
                            <template x-if="currentQuestion.type === 'multi'">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-lg bg-purple-50 text-purple-600 border border-purple-200">
                                    Несколько ответов (<span x-text="currentQuestion.count_answers"></span>)
                                </span>
                            </template>
                            <template x-if="currentQuestion.type === 'match'">
                                <span class="text-[10px] font-extrabold uppercase tracking-widest px-2.5 py-1 rounded-lg bg-teal-50 text-teal-600 border border-teal-200">
                                    Соответствие
                                </span>
                            </template>
                        </div>

                        {{-- Question text --}}
                        <div class="text-base font-semibold text-text leading-relaxed mb-7"
                             x-html="currentQuestion.text || ''"></div>

                        {{-- SELECT_ONE / IS_GROUP --}}
                        <template x-if="currentQuestion.type === 'one' || currentQuestion.type === 'group'">
                            <div class="space-y-2.5">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <button type="button" @click="answerQuestion(i)"
                                            class="w-full flex items-center gap-3 p-4 rounded-2xl border-2 text-left transition-all duration-150 cursor-pointer"
                                            :class="(currentQuestion.user_answers || []).includes(i)
                                                ? 'border-primary bg-primary/8 text-primary'
                                                : 'border-border hover:border-primary/40 hover:bg-gray-50 text-text'">
                                        <span class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-xs font-extrabold shrink-0 transition-all"
                                              :class="(currentQuestion.user_answers || []).includes(i)
                                                  ? 'border-primary bg-primary text-white'
                                                  : 'border-border text-text-muted'"
                                              x-text="String.fromCharCode(65 + i)"></span>
                                        <span class="flex-1 text-sm font-semibold leading-snug" x-html="v"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- SELECT_MULTI --}}
                        <template x-if="currentQuestion.type === 'multi'">
                            <div class="space-y-2.5">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <button type="button" @click="answerQuestion(i)"
                                            class="w-full flex items-center gap-3 p-4 rounded-2xl border-2 text-left transition-all duration-150 cursor-pointer"
                                            :class="(currentQuestion.user_answers || []).includes(i)
                                                ? 'border-primary bg-primary/8 text-primary'
                                                : 'border-border hover:border-primary/40 hover:bg-gray-50 text-text'">
                                        <span class="w-7 h-7 rounded-lg border-2 flex items-center justify-center shrink-0 transition-all"
                                              :class="(currentQuestion.user_answers || []).includes(i)
                                                  ? 'border-primary bg-primary text-white'
                                                  : 'border-border text-text-muted'">
                                            <template x-if="(currentQuestion.user_answers || []).includes(i)">
                                                <x-icon name="check" class="w-3.5 h-3.5" />
                                            </template>
                                        </span>
                                        <span class="flex-1 text-sm font-semibold leading-snug" x-html="v"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- IS_MATCH --}}
                        <template x-if="currentQuestion.type === 'match'">
                            <div class="space-y-4">
                                <p class="text-xs font-bold text-text-muted uppercase tracking-widest">Сопоставьте:</p>
                                <template x-for="pairIdx in [0, 1]" :key="pairIdx">
                                    <div class="flex items-start gap-3">
                                        <div class="flex-1 p-3 rounded-xl bg-gray-50 border border-border text-sm font-semibold text-text"
                                             x-html="currentQuestion.vars[pairIdx] || ''"></div>
                                        <span class="text-text-muted mt-3 shrink-0">→</span>
                                        <div class="flex-1 flex flex-col gap-1.5">
                                            <template x-for="optIdx in [4, 5, 6, 7]" :key="optIdx">
                                                <template x-if="currentQuestion.vars[optIdx]">
                                                    <button type="button"
                                                            @click="answerQuestion({ pair: pairIdx, val: optIdx - 4 })"
                                                            class="w-full p-2.5 rounded-xl border-2 text-sm text-left font-semibold transition-all cursor-pointer"
                                                            :class="(currentQuestion.user_answers || [])[pairIdx] === (optIdx - 4)
                                                                ? 'border-info bg-info-light text-info'
                                                                : 'border-border hover:border-info/40 text-text'">
                                                        <span class="font-mono text-xs mr-1.5 opacity-60"
                                                              x-text="String.fromCharCode(49 + (optIdx - 4))"></span>
                                                        <span x-html="currentQuestion.vars[optIdx] || ''"></span>
                                                    </button>
                                                </template>
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
                                Далее
                                <x-icon name="arrow-right" class="w-4 h-4" />
                            </button>
                        </div>

                    </div>
                </template>

                <template x-if="!currentQuestion">
                    <div class="flex flex-col items-center justify-center py-20 text-center">
                        <p class="text-text-muted font-semibold">Нет вопросов</p>
                    </div>
                </template>

            </div>
        </div>

    </div>

    {{-- ── Confirm finish modal ─────────────────────────────────────────── --}}
    <div x-show="confirmFinish" x-cloak
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="confirmFinish = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
            <h3 class="text-lg font-extrabold text-text mb-2">Завершить тест?</h3>

            <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl font-black"
                      :class="totalAnswered < totalQuestions ? 'text-danger' : 'text-success'"
                      x-text="totalAnswered"></span>
                <span class="text-text-muted font-semibold">из <span x-text="totalQuestions"></span> вопросов отвечено</span>
            </div>

            <template x-if="totalAnswered < totalQuestions">
                <p class="text-sm text-danger font-semibold mb-5">
                    <x-icon name="exclamation-circle" class="w-4 h-4 inline-block mr-1" />
                    <span x-text="totalQuestions - totalAnswered"></span> вопросов без ответа — засчитаются как неверные.
                </p>
            </template>

            <div class="flex gap-3 mt-2">
                <button @click="confirmFinish = false" class="flex-1 btn btn-outline btn-sm">Продолжить</button>
                <button @click="finishing = true; confirmFinish = false; autoFinish()"
                        :disabled="finishing"
                        class="flex-1 btn btn-danger btn-sm">
                    <template x-if="!finishing"><span>Завершить</span></template>
                    <template x-if="finishing"><span>Сохраняем...</span></template>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    const main = document.getElementById('main-content');
    if (main) { main.style.maxWidth = '100%'; main.style.padding = '0'; }
</script>
@endpush

@endsection
