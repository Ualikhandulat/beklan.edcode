@extends('layouts.student-test')
@section('title', 'Тестирование')

@section('content')

@php
    $access         = $test->access;
    $secondsLeft    = $test->secondsRemaining();
    $totalQuestions = collect($subjectsData)->sum(fn($s) => count($s['questions']));
    $answered       = collect($subjectsData)->sum('answered');
    $lsKey          = "edcode_test_{$test->id}_{$test->attempt_number}";
@endphp

@push('head')
<style>
    body { background: #F4F6F9; }
</style>
@endpush

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testApp', () => ({
            activeSubject: 0,
            activeQuestion: 0,
            finishing: false,
            confirmFinish: false,
            pendingSave: false,
            totalAnswered: {{ $answered }},
            totalQuestions: {{ $totalQuestions }},
            secondsLeft: {{ $secondsLeft ?? 'null' }},
            lsKey: '{{ $lsKey }}',

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
                // Restore from localStorage (answers selected since last server save)
                this.restoreLocal();

                // Auto-save every 60 seconds
                setInterval(() => { this.bulkSave(); }, 60000);

                if (this.secondsLeft === null) return;
                const tick = () => {
                    if (this.secondsLeft <= 0) { this.autoFinish(); return; }
                    this.secondsLeft--;
                    setTimeout(tick, 1000);
                };
                setTimeout(tick, 1000);
            },

            restoreLocal() {
                try {
                    const raw = localStorage.getItem(this.lsKey);
                    if (!raw) return;
                    const saved = JSON.parse(raw);
                    let restoredCount = 0;
                    saved.forEach((savedSubject, si) => {
                        if (!this.subjects[si]) return;
                        savedSubject.questions.forEach((savedQ, qi) => {
                            if (!this.subjects[si].questions[qi]) return;
                            const localAnswers = savedQ.user_answers || [];
                            if (localAnswers.length > 0) {
                                const wasAnswered = this.isAnswered(this.subjects[si].questions[qi]);
                                this.subjects[si].questions[qi].user_answers = localAnswers;
                                const nowAnswered = this.isAnswered(this.subjects[si].questions[qi]);
                                if (!wasAnswered && nowAnswered) {
                                    this.subjects[si].answered++;
                                    restoredCount++;
                                }
                            }
                        });
                    });
                    if (restoredCount > 0) {
                        this.totalAnswered = this.subjects.reduce((s, sub) => s + sub.answered, 0);
                        this.pendingSave = true; // Push restored answers to server
                    }
                } catch (e) {}
            },

            saveToLocal() {
                try {
                    localStorage.setItem(this.lsKey, JSON.stringify(
                        this.subjects.map(s => ({
                            questions: s.questions.map(q => ({ user_answers: q.user_answers || [] }))
                        }))
                    ));
                } catch (e) {}
            },

            clearLocal() {
                try { localStorage.removeItem(this.lsKey); } catch (e) {}
            },

            async autoFinish() {
                if (this.finishing) return;
                this.finishing = true;
                await this.bulkSave(true);
                fetch('{{ route('student.test.finish', $test) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(d => {
                    this.clearLocal();
                    window.location = d.redirect;
                });
            },

            async bulkSave(force = false) {
                if (!force && !this.pendingSave) return;
                this.pendingSave = false;
                const payload = {
                    subjects: this.subjects.map(s => ({
                        test_subject_id: s.test_subject_id,
                        questions: s.questions.map(q => ({
                            detail_id: q.detail_id,
                            user_answers: (q.user_answers || []).filter(a => a !== null),
                        })),
                    })),
                };
                try {
                    await fetch('{{ route('student.test.save', $test) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                        body: JSON.stringify(payload),
                    });
                    this.clearLocal(); // Saved to server — localStorage no longer needed
                } catch {
                    this.pendingSave = true;
                }
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

                this.pendingSave = true;
                this.saveToLocal(); // Save immediately to localStorage
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
                                    <button type="button" @click="answerQuestion(i)"
                                            class="w-full flex items-center gap-3 p-3 rounded-2xl border-2 text-left transition-all duration-150 cursor-pointer"
                                            :class="(currentQuestion.user_answers || []).includes(i)
                                                ? 'border-primary bg-primary/8'
                                                : 'border-border hover:border-primary/40 hover:bg-gray-50'">
                                        <span class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-xs font-extrabold shrink-0 transition-all"
                                              :class="(currentQuestion.user_answers || []).includes(i)
                                                  ? 'border-primary bg-primary text-white'
                                                  : 'border-border text-text-muted'"
                                              x-text="String.fromCharCode(65 + i)"></span>
                                        <span class="flex-1 text-sm font-semibold text-gray-900 leading-snug" x-html="v"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- SELECT_MULTI --}}
                        <template x-if="currentQuestion.type === 'multi'">
                            <div class="space-y-2">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <button type="button" @click="answerQuestion(i)"
                                            class="w-full flex items-center gap-3 p-3 rounded-2xl border-2 text-left transition-all duration-150 cursor-pointer"
                                            :class="(currentQuestion.user_answers || []).includes(i)
                                                ? 'border-primary bg-primary/8'
                                                : 'border-border hover:border-primary/40 hover:bg-gray-50'">
                                        <span class="w-7 h-7 rounded-lg border-2 flex items-center justify-center shrink-0 transition-all"
                                              :class="(currentQuestion.user_answers || []).includes(i)
                                                  ? 'border-primary bg-primary text-white'
                                                  : 'border-border text-text-muted'">
                                            <template x-if="(currentQuestion.user_answers || []).includes(i)">
                                                <x-icon name="check" class="w-3.5 h-3.5" />
                                            </template>
                                        </span>
                                        <span class="flex-1 text-sm font-semibold text-gray-900 leading-snug" x-html="v"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- IS_MATCH — custom dropdown showing full option text --}}
                        <template x-if="currentQuestion.type === 'match'">
                            <div class="space-y-3">

                                {{-- All options reference --}}
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
                                    <div class="rounded-2xl border-2 p-4 transition-all"
                                         :class="(currentQuestion.user_answers ?? [])[pairIdx] != null
                                             ? 'border-primary/40 bg-primary/4'
                                             : 'border-border bg-white'">

                                        {{-- Left item text --}}
                                        <div class="flex items-start gap-2.5 mb-3">
                                            <span class="w-6 h-6 rounded-lg bg-gray-100 text-gray-700 text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5"
                                                  x-text="pairIdx + 1"></span>
                                            <div class="text-sm font-bold text-gray-900 leading-snug flex-1"
                                                 x-html="currentQuestion.vars[pairIdx] || ''"></div>
                                        </div>

                                        {{-- Custom dropdown --}}
                                        <div x-data="{ open: false }" class="relative" @keydown.escape="open = false">

                                            {{-- Trigger --}}
                                            <button type="button"
                                                    @click="open = !open"
                                                    class="w-full flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl border-2 text-left transition-colors"
                                                    :class="(currentQuestion.user_answers ?? [])[pairIdx] != null
                                                        ? 'border-primary bg-white text-gray-900'
                                                        : 'border-border bg-white text-text-muted hover:border-primary/60'">

                                                <template x-if="(currentQuestion.user_answers ?? [])[pairIdx] != null">
                                                    <span class="w-5 h-5 rounded-md bg-primary/15 text-primary text-xs font-extrabold flex items-center justify-center shrink-0"
                                                          x-text="['А','Б','В','Г'][(currentQuestion.user_answers ?? [])[pairIdx]] ?? ''"></span>
                                                </template>

                                                <span class="flex-1 leading-snug"
                                                      x-html="(currentQuestion.user_answers ?? [])[pairIdx] != null
                                                          ? (currentQuestion.vars[4 + (currentQuestion.user_answers ?? [])[pairIdx]] || '')
                                                          : '— Выберите ответ —'"></span>

                                                <svg class="w-4 h-4 text-text-muted shrink-0 transition-transform duration-200"
                                                     :class="open ? 'rotate-180' : ''"
                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </button>

                                            {{-- Dropdown panel --}}
                                            <div x-show="open"
                                                 x-transition:enter="transition ease-out duration-100"
                                                 x-transition:enter-start="opacity-0 scale-95"
                                                 x-transition:enter-end="opacity-100 scale-100"
                                                 x-transition:leave="transition ease-in duration-75"
                                                 x-transition:leave-start="opacity-100 scale-100"
                                                 x-transition:leave-end="opacity-0 scale-95"
                                                 @click.outside="open = false"
                                                 class="absolute left-0 right-0 top-full mt-1 bg-white rounded-xl border border-border shadow-lg z-20 overflow-hidden">
                                                <template x-for="(letter, optIdx) in ['А', 'Б', 'В', 'Г']" :key="optIdx">
                                                    <button type="button"
                                                            @click="answerQuestion({ pair: pairIdx, val: optIdx }); open = false"
                                                            class="w-full flex items-start gap-3 px-3.5 py-2.5 text-left transition-colors border-b last:border-0 border-border/60"
                                                            :class="(currentQuestion.user_answers ?? [])[pairIdx] === optIdx
                                                                ? 'bg-primary/8 text-primary'
                                                                : 'hover:bg-gray-50 text-gray-900'">
                                                        <span class="w-5 h-5 rounded-md text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                                                              :class="(currentQuestion.user_answers ?? [])[pairIdx] === optIdx
                                                                  ? 'bg-primary text-white'
                                                                  : 'bg-gray-100 text-gray-600'"
                                                              x-text="letter"></span>
                                                        <span class="flex-1 text-sm font-semibold leading-snug"
                                                              x-html="currentQuestion.vars[4 + optIdx] || ''"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <p class="text-xs text-center text-text-muted font-semibold">
                                    Для каждого пункта выберите соответствующий вариант (А, Б, В или Г)
                                </p>
                            </div>
                        </template>

                        {{-- Navigation --}}
                        <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
                            <button type="button"
                                    @click="activeQuestion > 0 ? activeQuestion-- : (activeSubject > 0 ? (activeSubject--, activeQuestion = subjects[activeSubject].questions.length - 1) : null)"
                                    class="btn btn-outline btn-sm"
                                    :disabled="activeSubject === 0 && activeQuestion === 0">
                                <x-icon name="arrow-left" class="w-4 h-4" />
                                Предыдущий
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
                                Следующий
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
                <button @click="confirmFinish = false; autoFinish()"
                        :disabled="finishing"
                        class="flex-1 btn btn-danger btn-sm">
                    <template x-if="!finishing"><span>Завершить</span></template>
                    <template x-if="finishing"><span>Сохраняем...</span></template>
                </button>
            </div>
        </div>
    </div>

</div>


@endsection
