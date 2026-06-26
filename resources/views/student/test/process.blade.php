@extends('layouts.student-test')
@section('title', __('Тестирование'))

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

@push('nav-right')
<div
    x-data="{
        elapsedSeconds: {{ $test->started_at ? (int) $test->started_at->diffInSeconds(now()) : 0 }},
        get elapsedStr() {
            const h = Math.floor(this.elapsedSeconds / 3600);
            const m = Math.floor((this.elapsedSeconds % 3600) / 60);
            const s = this.elapsedSeconds % 60;
            if (h > 0) return String(h).padStart(2, '0') + ':' + String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
            return String(m).padStart(2, '0') + ':' + String(s).padStart(2, '0');
        },
    }"
    x-init="setInterval(() => elapsedSeconds++, 1000)"
    class="ml-auto flex items-center gap-1.5 px-2.5 py-1 rounded-full border border-border bg-gray-50 text-xs text-text"
>
    <x-icon name="clock" class="w-3 h-3 shrink-0" />
    <span class="font-mono font-extrabold tabular-nums" x-text="elapsedStr"></span>
</div>
@endpush

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('testApp', () => ({
            activeSubject: 0,
            activeQuestion: 0,
            finishing: false,
            confirmFinish: false,
            calcOpen: false,
            mendeleevOpen: false,
            solubilityOpen: false,
            pendingChanges: [],
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
                    if (!Array.isArray(saved) || saved.length === 0) return;
                    saved.forEach(change => {
                        const subject = this.subjects.find(s => s.test_subject_id === change.test_subject_id);
                        if (!subject) return;
                        const q = subject.questions.find(q => q.detail_id === change.detail_id);
                        if (!q) return;
                        const wasAnswered = this.isAnswered(q);
                        q.user_answers = change.user_answers;
                        if (!wasAnswered && this.isAnswered(q)) {
                            subject.answered++;
                        }
                    });
                    this.totalAnswered = this.subjects.reduce((s, sub) => s + sub.answered, 0);
                    this.pendingChanges = saved;
                } catch (e) {}
            },

            saveToLocal() {
                try {
                    localStorage.setItem(this.lsKey, JSON.stringify(this.pendingChanges));
                } catch (e) {}
            },

            clearLocal() {
                try { localStorage.removeItem(this.lsKey); } catch (e) {}
            },

            async autoFinish() {
                if (this.finishing) return;
                this.finishing = true;
                await this.bulkSave();
                fetch('{{ route('student.test.finish', $test) }}', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                }).then(r => r.json()).then(d => {
                    this.clearLocal();
                    window.location = d.redirect;
                });
            },

            async bulkSave() {
                if (this.pendingChanges.length === 0) return;

                const snapshot = this.pendingChanges.splice(0);

                const bySubject = {};
                for (const c of snapshot) {
                    if (!bySubject[c.test_subject_id]) {
                        bySubject[c.test_subject_id] = { test_subject_id: c.test_subject_id, questions: [] };
                    }
                    bySubject[c.test_subject_id].questions.push({
                        detail_id: c.detail_id,
                        // Keep positions intact for IS_MATCH (index 0 = pair 1, index 1 = pair 2):
                        // filtering out unanswered (null) pairs would shift answers into the wrong slot.
                        user_answers: c.user_answers || [],
                    });
                }

                try {
                    const r = await fetch('{{ route('student.test.save', $test) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' },
                        body: JSON.stringify({ subjects: Object.values(bySubject) }),
                    });
                    if (!r.ok) { throw new Error('save failed'); }
                    this.clearLocal();
                } catch {
                    this.pendingChanges.unshift(...snapshot);
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
                    const maxSelectable = (q.vars && q.vars.length) || 6;
                    if (idx >= 0) { answers.splice(idx, 1); } else if (answers.length < maxSelectable) { answers.push(answer); }
                } else if (type === 'match') {
                    answers = [...(q.user_answers || [null, null])];
                    while (answers.length < 2) answers.push(null);
                    answers[answer.pair] = answer.val;
                }

                const wasAnswered = this.isAnswered(q);
                q.user_answers = answers;
                this.subjects[this.activeSubject].questions[this.activeQuestion].user_answers = answers;
                const isNowAnswered = this.isAnswered(q);

                if (!wasAnswered && isNowAnswered) {
                    this.subjects[this.activeSubject].answered++;
                    this.totalAnswered++;
                } else if (wasAnswered && !isNowAnswered) {
                    this.subjects[this.activeSubject].answered--;
                    this.totalAnswered--;
                }

                const subjectId = this.subjects[this.activeSubject].test_subject_id;
                const pendingIdx = this.pendingChanges.findIndex(
                    c => c.test_subject_id === subjectId && c.detail_id === q.detail_id
                );
                if (pendingIdx >= 0) {
                    this.pendingChanges[pendingIdx].user_answers = answers;
                } else {
                    this.pendingChanges.push({ test_subject_id: subjectId, detail_id: q.detail_id, user_answers: answers });
                }
                this.saveToLocal();
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

    {{-- ── Subject — mobile only ────────────────────────────────── --}}
    @if (count($subjectsData) === 1)
    <div class="lg:hidden bg-white border-b border-border px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5 min-w-0">
            <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
            <span class="text-sm font-bold text-text truncate">{{ $subjectsData[0]['subject']->title }}</span>
        </div>
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary shrink-0"
              x-text="`${currentSubject?.answered ?? 0}/${currentSubject?.questions.length ?? 0}`"></span>
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
                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-primary/10 text-primary"
                      x-text="`${currentSubject?.answered ?? 0}/${currentSubject?.questions.length ?? 0}`"></span>
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
                          :class="si === activeSubject ? 'bg-primary/15 text-primary' : (subject.answered > 0 ? 'bg-success/15 text-success' : 'bg-gray-100 text-text-muted')"
                          x-text="`${subject.answered}/${subject.questions.length}`"></span>
                </button>
            </template>
        </div>
    </div>
    @endif

    {{-- ── Test tools — mobile ──────────────────────────────────────────── --}}
    <div class="lg:hidden bg-white border-b border-border px-4 py-2.5">
        @include('student.test._tools')
    </div>

    {{-- ── Question pills — mobile horizontal scroll ───────────────────── --}}
    <div class="lg:hidden bg-white border-b border-border overflow-x-auto mt-3 border-y">
        <div class="flex gap-2 px-4 py-2.5" style="width: max-content; min-width: 100%">
            <template x-for="(q, qi) in currentSubject?.questions ?? []" :key="qi">
                <button type="button"
                        @click="goTo(activeSubject, qi)"
                        class="w-9 h-9 rounded-xl text-xs font-extrabold shrink-0 transition-all duration-150 cursor-pointer"
                        :class="qi === activeQuestion
                            ? 'bg-primary text-white shadow-sm scale-110'
                            : (isAnswered(q)
                                ? 'bg-success/20 text-success border border-success/30'
                                : 'bg-gray-100 border border-gray-300 text-black hover:bg-gray-200')"
                        x-text="qi + 1">
                </button>
            </template>
        </div>
    </div>

    {{-- ── Main area ────────────────────────────────────────────────────── --}}
    <div class="flex-1 max-w-7xl mx-auto w-full px-0 lg:px-6 lg:py-5 flex flex-col lg:flex-row gap-0 lg:gap-4 mt-3 md:mt-0">

        {{-- ── Desktop sidebar: subjects + pills + finish ──────────────── --}}
        <div class="hidden lg:flex lg:flex-col gap-3 lg:w-72 xl:w-80 shrink-0"
             style="position: sticky; top: 4.5rem; align-self: start; max-height: calc(100vh - 5.5rem); overflow-y: auto">

            {{-- Subject list — vertical block --}}
            @if (count($subjectsData) === 1)
            <div class="bg-white rounded-2xl border border-border px-4 py-3 flex items-center gap-3" style="box-shadow: var(--shadow-card)">
                <span class="w-2 h-2 rounded-full bg-primary shrink-0"></span>
                <span class="text-sm font-bold text-text flex-1 truncate">{{ $subjectsData[0]['subject']->title }}</span>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-primary/10 text-primary shrink-0"
                      x-text="`${currentSubject?.answered ?? 0}/${currentSubject?.questions.length ?? 0}`"></span>
            </div>
            @else
            <div class="bg-white rounded-2xl border border-border overflow-hidden" style="box-shadow: var(--shadow-card)">
                <template x-for="(subject, si) in subjects" :key="si">
                    <button type="button"
                            @click="activeSubject = si; activeQuestion = 0"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-left transition-colors border-b border-border last:border-b-0 cursor-pointer"
                            :class="si === activeSubject
                                ? 'bg-primary text-white'
                                : 'text-text hover:bg-gray-50'">
                        <span class="w-2 h-2 rounded-full shrink-0 transition-all"
                              :class="si === activeSubject ? 'bg-white' : 'bg-border'"></span>
                        <span x-text="subject.subject.title" class="flex-1 truncate"></span>
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full shrink-0"
                              :class="si === activeSubject
                                  ? 'bg-white/25 text-white'
                                  : (subject.answered > 0 ? 'bg-success/15 text-success' : 'bg-gray-100 text-text-muted')"
                              x-text="`${subject.answered}/${subject.questions.length}`"></span>
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
                                :class="qi === activeQuestion
                                    ? 'bg-primary text-white shadow-sm scale-110'
                                    : (isAnswered(q)
                                        ? 'bg-success/20 text-success border border-success/30 hover:bg-success/30'
                                        : 'bg-gray-100 border border-gray-300 text-black hover:bg-gray-200')"
                                x-text="qi + 1">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Test tools --}}
            <div class="bg-white rounded-2xl border border-border overflow-hidden" style="box-shadow: var(--shadow-card)">
                <button type="button" @click="calcOpen = true"
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-left text-text hover:bg-gray-50 transition-colors border-b border-border last:border-b-0 cursor-pointer">
                    <x-icon name="calculator" class="w-4 h-4 text-text-muted shrink-0" />
                    <span>{{ __('Калькулятор') }}</span>
                </button>
                <button type="button" @click="mendeleevOpen = true"
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-left text-text hover:bg-gray-50 transition-colors border-b border-border last:border-b-0 cursor-pointer">
                    <x-icon name="beaker" class="w-4 h-4 text-text-muted shrink-0" />
                    <span>{{ __('Менделеев') }}</span>
                </button>
                <button type="button" @click="solubilityOpen = true"
                        class="w-full flex items-center gap-3 px-4 py-3 text-sm font-bold text-left text-text hover:bg-gray-50 transition-colors cursor-pointer">
                    <x-icon name="beaker" class="w-4 h-4 text-text-muted shrink-0" />
                    <span>{{ __('Растворимость') }}</span>
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-border p-4" style="box-shadow: var(--shadow-card)">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-semibold text-text-muted">
                        <span x-text="totalAnswered" class="text-text font-extrabold text-sm"></span> / <span x-text="totalQuestions"></span> {{ __('отв.') }}
                    </span>
                    <template x-if="timerStr !== null">
                        <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs transition-all duration-300"
                             :class="timerUrgent ? 'border-danger/40 bg-danger/8 text-danger' : 'border-border bg-gray-50 text-text'">
                            <x-icon name="clock" class="w-3 h-3 shrink-0" />
                            <span class="font-mono font-extrabold tabular-nums" x-text="timerStr"></span>
                        </div>
                    </template>
                </div>
                <div class="h-1.5 rounded-full bg-gray-100 overflow-hidden mb-3">
                    <div class="h-full rounded-full transition-all duration-300"
                         :class="totalAnswered >= totalQuestions ? 'bg-success' : 'bg-primary'"
                         :style="`width: ${totalQuestions > 0 ? Math.round(totalAnswered / totalQuestions * 100) : 0}%`"></div>
                </div>
                <button @click="confirmFinish = true" class="w-full btn btn-danger">
                    <x-icon name="check" class="w-4 h-4" />
                    {{ __('Завершить тест') }}
                </button>
            </div>

        </div>

        {{-- ── Question card ───────────────────────────────────────────── --}}
        <div class="flex-1 min-w-0 pb-24 lg:pb-0">
            <div class="bg-white rounded-none lg:rounded-2xl border-t lg:border border-border min-h-full"
                 style="box-shadow: var(--shadow-card)">

                <template x-if="currentQuestion">
                    <div class="p-5 sm:p-7">

                        {{-- Context passage (IS_GROUP) --}}
                        <template x-if="currentQuestion.context">
                            <div class="text-sm text-text leading-relaxed mb-4"
                                 x-html="currentQuestion.context"></div>
                        </template>

                        {{-- Question text --}}
                        <div class="text-base font-semibold text-text leading-relaxed mb-3"
                             x-html="currentQuestion.text || ''"></div>

                        {{-- Question type label --}}
                        <p x-show="currentQuestion.type !== 'group'"
                           class="text-xs font-semibold text-text-muted mb-5"
                           x-text="currentQuestion.type === 'multi'
                               ? @js(__('Выберите один или несколько вариантов'))
                               : currentQuestion.type === 'match' ? @js(__('Соответствие'))
                               : @js(__('Один верный ответ'))">
                        </p>

                        {{-- SELECT_ONE / IS_GROUP --}}
                        <template x-if="currentQuestion.type === 'one' || currentQuestion.type === 'group'">
                            <div class="space-y-2">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <button type="button" @click="answerQuestion(i + 1)"
                                            class="w-full flex items-center gap-3 p-3 rounded-2xl border-2 text-left transition-all duration-150 cursor-pointer"
                                            :class="(currentQuestion.user_answers || []).includes(i + 1)
                                                ? 'border-primary bg-primary/8'
                                                : 'border-border hover:border-primary/40 hover:bg-gray-50'">
                                        <span class="w-7 h-7 rounded-full border-2 flex items-center justify-center text-xs font-extrabold shrink-0 transition-all"
                                              :class="(currentQuestion.user_answers || []).includes(i + 1)
                                                  ? 'border-primary bg-primary text-white'
                                                  : 'border-border text-text-muted'"
                                              x-text="String.fromCharCode(65 + i)"></span>
                                        <span class="flex-1 text-sm font-semibold text-black leading-snug" x-html="v"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- SELECT_MULTI --}}
                        <template x-if="currentQuestion.type === 'multi'">
                            <div class="space-y-2">
                                <template x-for="(v, i) in currentQuestion.vars" :key="i">
                                    <button type="button" @click="answerQuestion(i + 1)"
                                            class="w-full flex items-center gap-3 p-3 rounded-2xl border-2 text-left transition-all duration-150 cursor-pointer"
                                            :class="(currentQuestion.user_answers || []).includes(i + 1)
                                                ? 'border-primary bg-primary/8'
                                                : 'border-border hover:border-primary/40 hover:bg-gray-50'">
                                        <span class="w-7 h-7 rounded-lg border-2 flex items-center justify-center shrink-0 transition-all"
                                              :class="(currentQuestion.user_answers || []).includes(i + 1)
                                                  ? 'border-primary bg-primary text-white'
                                                  : 'border-border text-text-muted'">
                                            <template x-if="(currentQuestion.user_answers || []).includes(i + 1)">
                                                <x-icon name="check" class="w-3.5 h-3.5" />
                                            </template>
                                        </span>
                                        <span class="flex-1 text-sm font-semibold text-black leading-snug" x-html="v"></span>
                                    </button>
                                </template>
                            </div>
                        </template>

                        {{-- IS_MATCH — custom dropdown showing full option text --}}
                        <template x-if="currentQuestion.type === 'match'">
                            <div class="space-y-3">
                                {{-- Pair rows --}}
                                <template x-for="pairIdx in [0, 1]" :key="pairIdx">
                                    <div class="rounded-2xl border-2 p-4 transition-all"
                                         :class="(currentQuestion.user_answers ?? [])[pairIdx] != null
                                             ? 'border-primary/40 bg-primary/4'
                                             : 'border-border bg-white'">

                                        {{-- Left item text --}}
                                        <div class="flex items-start gap-2.5 mb-3">
                                            <span class="w-6 h-6 rounded-lg bg-gray-100 text-black text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5"
                                                  x-text="pairIdx + 1"></span>
                                            <div class="text-sm font-bold text-black leading-snug flex-1"
                                                 x-html="currentQuestion.vars[pairIdx] || ''"></div>
                                        </div>

                                        {{-- Custom dropdown --}}
                                        <div x-data="{ open: false }" class="relative" @keydown.escape="open = false">

                                            {{-- Trigger --}}
                                            <button type="button"
                                                    @click="open = !open"
                                                    class="w-full flex items-center gap-3 px-3.5 py-2.5 text-sm font-semibold rounded-xl border-2 text-left transition-colors"
                                                    :class="(currentQuestion.user_answers ?? [])[pairIdx] != null
                                                        ? 'border-primary bg-white text-black'
                                                        : 'border-border bg-white text-text-muted hover:border-primary/60'">

                                                <template x-if="(currentQuestion.user_answers ?? [])[pairIdx] != null">
                                                    <span class="w-5 h-5 rounded-md bg-primary/15 text-primary text-xs font-extrabold flex items-center justify-center shrink-0"
                                                          x-text="['А','Б','В','Г'][(currentQuestion.user_answers ?? [])[pairIdx] - 3] ?? ''"></span>
                                                </template>

                                                <span class="flex-1 leading-snug"
                                                      x-html="(currentQuestion.user_answers ?? [])[pairIdx] != null
                                                          ? (currentQuestion.vars[(currentQuestion.user_answers ?? [])[pairIdx] - 1] || '')
                                                          : @js(__('— Выберите ответ —'))"></span>

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
                                                            @click="answerQuestion({ pair: pairIdx, val: 3 + optIdx }); open = false"
                                                            class="w-full flex items-start gap-3 px-3.5 py-2.5 text-left transition-colors border-b last:border-0 border-border/60"
                                                            :class="(currentQuestion.user_answers ?? [])[pairIdx] === (3 + optIdx)
                                                                ? 'bg-primary/8 text-primary'
                                                                : 'hover:bg-gray-50 text-black'">
                                                        <span class="w-5 h-5 rounded-md text-xs font-extrabold flex items-center justify-center shrink-0 mt-0.5 transition-colors"
                                                              :class="(currentQuestion.user_answers ?? [])[pairIdx] === (3 + optIdx)
                                                                  ? 'bg-primary text-white'
                                                                  : 'bg-gray-100 text-black'"
                                                              x-text="letter"></span>
                                                        <span class="flex-1 text-sm font-semibold leading-snug"
                                                              x-html="currentQuestion.vars[2 + optIdx] || ''"></span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <p class="text-xs text-center text-text-muted font-semibold">
                                    {{ __('Для каждого пункта выберите соответствующий вариант (А, Б, В или Г)') }}
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
                                {{ __('Предыдущий') }}
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
                                {{ __('Следующий') }}
                                <x-icon name="arrow-right" class="w-4 h-4" />
                            </button>
                        </div>

                    </div>
                </template>

                <template x-if="!currentQuestion">
                    <div class="flex flex-col items-center justify-center py-20 text-center">
                        <p class="text-text-muted font-semibold">{{ __('Нет вопросов') }}</p>
                    </div>
                </template>

            </div>
        </div>

    </div>

    {{-- ── Mobile sticky bottom bar ────────────────────────────────────── --}}
    <div class="lg:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-border z-30"
         style="box-shadow: 0 -4px 16px rgba(0,0,0,0.08)">
        <div class="h-1 bg-gray-100 overflow-hidden">
            <div class="h-full transition-all duration-300"
                 :class="totalAnswered >= totalQuestions ? 'bg-success' : 'bg-primary'"
                 :style="`width: ${totalQuestions > 0 ? Math.round(totalAnswered / totalQuestions * 100) : 0}%`"></div>
        </div>
        <div class="flex items-center gap-3 px-4 py-3">
            <span class="text-xs font-semibold text-text-muted shrink-0">
                <span x-text="totalAnswered" class="text-text font-extrabold"></span>/<span x-text="totalQuestions"></span>
            </span>
            <template x-if="timerStr !== null">
                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs transition-all shrink-0"
                     :class="timerUrgent ? 'border-danger/40 bg-danger/8 text-danger' : 'border-border bg-gray-50 text-text'">
                    <x-icon name="clock" class="w-3 h-3 shrink-0" />
                    <span class="font-mono font-extrabold tabular-nums" x-text="timerStr"></span>
                </div>
            </template>
            <button @click="confirmFinish = true" class="ml-auto btn btn-danger btn-sm">
                <x-icon name="check" class="w-4 h-4" />
                {{ __('Завершить') }}
            </button>
        </div>
    </div>

    {{-- ── Confirm finish modal ─────────────────────────────────────────── --}}
    <div x-show="confirmFinish" x-cloak
         class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
         @click.self="confirmFinish = false">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6" @click.stop>
            <h3 class="text-lg font-extrabold text-text mb-2">{{ __('Завершить тест?') }}</h3>

            <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl font-black"
                      :class="totalAnswered < totalQuestions ? 'text-danger' : 'text-success'"
                      x-text="totalAnswered"></span>
                <span class="text-text-muted font-semibold"
                      x-text="@js(__('из :count вопросов отвечено')).replace(':count', totalQuestions)"></span>
            </div>

            <template x-if="totalAnswered < totalQuestions">
                <p class="text-sm text-danger font-semibold mb-5">
                    <x-icon name="exclamation-circle" class="w-4 h-4 inline-block mr-1" />
                    <span x-text="@js(__(':count вопросов без ответа — засчитаются как неверные.')).replace(':count', totalQuestions - totalAnswered)"></span>
                </p>
            </template>

            <div class="flex gap-3 mt-2">
                <button @click="confirmFinish = false" class="flex-1 btn btn-outline btn-sm">{{ __('Продолжить') }}</button>
                <button @click="confirmFinish = false; autoFinish()"
                        :disabled="finishing"
                        class="flex-1 btn btn-danger btn-sm">
                    <template x-if="!finishing"><span>{{ __('Завершить') }}</span></template>
                    <template x-if="finishing"><span>{{ __('Сохраняем...') }}</span></template>
                </button>
            </div>
        </div>
    </div>

    {{-- ── Test tools modals ─────────────────────────────────────────────── --}}
    <x-calculator show="calcOpen" />

    <x-modal :title="__('Таблица Менделеева')" show="mendeleevOpen" size="2xl">
        <div class="p-3">
            <x-zoomable-image :src="asset('images/mendeleev.png')" :alt="__('Периодическая таблица Менделеева')" />
        </div>
    </x-modal>

    <x-modal :title="__('Таблица растворимости')" show="solubilityOpen" size="2xl">
        <div class="p-3">
            <x-zoomable-image :src="asset('images/solubility.png')" :alt="__('Таблица растворимости')" />
        </div>
    </x-modal>

</div>


@endsection
