@extends('layouts.admin')

@section('content')

@php
    $isEdit     = $access->exists;
    $oldTarget  = old('user_id') || ($isEdit && $access->user_id) ? 'user' : 'group';
    $oldType    = old('type', $access->type?->value ?? '');
    $oldSCS     = (bool) old('student_chooses_subject', $access->student_chooses_subject ?? false);
    $oldQCount  = old('question_count',  $access->question_count  ?? 0);
    $oldTries   = old('attempts_limit',  $access->attempts_limit  ?? 1);
    $oldExpiry  = old('expires_at',      $access->expires_at?->format('Y-m-d\TH:i') ?? '');
    $oldNusqaMode   = old('nusqa_mode',   $nusqaMode);
    $oldNusqaNumber = old('nusqa_number', $access->nusqa_number ?? '');
@endphp

<div class="max-w-5xl mx-auto"
     x-data="{
         targetType: '{{ $oldTarget }}',
         accessType: '{{ $oldType }}',
         scs: {{ $oldSCS ? 'true' : 'false' }},
         data: @js($subjectsWithParts),
         mandatory: @js($mandatorySubjectsJs),

         // ENT elective slots
         electives: @js([
             ['subjectId' => $existingElectives->get(0)?->subject_id ?? null],
             ['subjectId' => $existingElectives->get(1)?->subject_id ?? null],
         ]),

         // ENT нұсқа
         nusqaMode: '{{ $oldNusqaMode }}',
         nusqaNumber: '{{ $oldNusqaNumber }}',

         // Subject type
         single: @js([
             'subjectId' => $existingSingle?->subject_id ?? null,
             'partType'  => $existingSingle?->part_type?->value ?? '',
             'partId'    => $existingSingle?->part_id ?? null,
             'scp'       => (bool) ($existingSingle?->student_chooses_part ?? false),
         ]),

         // Compute nusqa lookup: {subjectId: {nusqaTitle: count}}
         get nusqaLookup() {
             const r = {};
             for (const [id, s] of Object.entries(this.data)) {
                 r[id] = {};
                 for (const n of (s.nusqas || [])) r[id][n.title] = n.count;
             }
             return r;
         },

         get nusqaValidation() {
             if (this.nusqaMode !== 'specific' || !this.nusqaNumber) return [];
             const num = String(this.nusqaNumber).trim();
             const subjects = [...this.mandatory];
             if (!this.scs) {
                 for (const e of this.electives) {
                     if (e.subjectId) subjects.push({id: e.subjectId, title: this.data[e.subjectId]?.title ?? '?'});
                 }
             }
             return subjects.map(s => ({
                 id: s.id,
                 title: s.title,
                 count: this.nusqaLookup[s.id]?.[num] ?? null,
             }));
         },
         get nusqaAllFound() {
             const v = this.nusqaValidation;
             return v.length > 0 && v.every(s => s.count !== null);
         },

         // Subject type: filtered parts
         get subjectNusqas() {
             return this.data[this.single.subjectId]?.nusqas ?? [];
         },
         get subjectTopics() {
             return this.data[this.single.subjectId]?.topics ?? [];
         },

         get electiveSubjects() {
             const mandatoryIds = new Set(this.mandatory.map(s => String(s.id)));
             return Object.fromEntries(
                 Object.entries(this.data).filter(([id]) => !mandatoryIds.has(id))
             );
         },

         onAccessTypeChange() {
             this.scs = false;
             this.electives = [{subjectId:null},{subjectId:null}];
             this.nusqaMode = 'random';
             this.nusqaNumber = '';
             this.single = {subjectId:null,partType:'',partId:null,scp:false};
         }
     }">

    <form method="POST" action="{{ $action }}">
        @csrf
        @if ($method !== 'POST') @method($method) @endif

        @if ($errors->any())
            <div class="mb-5 p-4 bg-danger-light border border-danger/20 rounded-2xl flex items-start gap-3">
                <x-icon name="exclamation-circle" class="w-5 h-5 text-danger shrink-0 mt-0.5" />
                <div>
                    <p class="font-bold text-danger text-sm mb-1.5">Исправьте ошибки:</p>
                    <ul class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm text-danger">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="flex flex-col lg:flex-row gap-5 items-start">

            {{-- ── LEFT COLUMN ─────────────────────────────────────────────────── --}}
            <div class="w-full lg:w-96 shrink-0 space-y-4">

                {{-- 1. Кому ──────────────────────────────────────────────────────── --}}
                <div class="card">
                    <p class="q-section-label mb-3">Кому выдать доступ</p>

                    <div class="flex gap-2 mb-4">
                        <button type="button" @click="targetType = 'user'"
                            :class="targetType==='user' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm text-text-muted'">
                            <x-icon name="user" class="w-4 h-4" /> Студент
                        </button>
                        <button type="button" @click="targetType = 'group'"
                            :class="targetType==='group' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm text-text-muted'">
                            <x-icon name="user-group" class="w-4 h-4" /> Группа
                        </button>
                    </div>

                    <div x-show="targetType === 'user'" x-cloak>
                        <x-form.select name="user_id" label="Студент"
                            :options="$students" :selected="old('user_id', $access->user_id)"
                            placeholder="Выберите студента..." :required="true" />
                    </div>
                    <div x-show="targetType === 'group'" x-cloak>
                        <x-form.select name="group_id" label="Группа"
                            :options="$groups" :selected="old('group_id', $access->group_id)"
                            placeholder="Выберите группу..." :required="true" />
                    </div>
                </div>

                {{-- 2. Тип теста ───────────────────────────────────────────────── --}}
                <div class="card">
                    <p class="q-section-label mb-3">Тип теста</p>
                    <input type="hidden" name="type" :value="accessType">
                    <div class="flex gap-2">
                        @foreach ($types as $val => $label)
                            <button type="button"
                                @click="accessType = '{{ $val }}'; onAccessTypeChange()"
                                :class="accessType==='{{ $val }}'
                                    ? 'btn btn-primary'
                                    : 'btn btn-outline text-text-muted'">
                                {{ $label }}
                            </button>
                        @endforeach
                    </div>
                    @error('type') <p class="form-error mt-2">{{ $message }}</p> @enderror
                </div>

                {{-- 5. Ограничения ─────────────────────────────────────────────── --}}
                <div x-show="accessType !== ''" x-cloak class="card">
                    <p class="q-section-label mb-3">Ограничения</p>
                    <div class="space-y-4">
                        <div x-show="accessType === 'subject'" x-cloak>
                            <x-form.input name="question_count" label="Вопросов"
                                type="number" placeholder="0 — все" :value="$oldQCount" />
                        </div>
                        <input x-show="accessType !== 'subject'" type="hidden" name="question_count" value="0">
                        <x-form.input name="attempts_limit" label="Попыток"
                            type="number" placeholder="1" :value="$oldTries" />
                        <x-form.input name="expires_at" label="Срок доступа"
                            type="datetime-local" :value="$oldExpiry" />
                    </div>
                    <p class="text-xs text-text-muted mt-3">Попыток: 0 = без ограничений.</p>
                </div>

            </div>

            {{-- ── RIGHT COLUMN ─────────────────────────────────────────────────── --}}
            <div class="flex-1 min-w-0 space-y-4">

                {{-- Empty state (no type selected) --}}
                <div x-show="accessType === ''" x-cloak
                     class="card flex flex-col items-center justify-center py-16 text-center">
                    <x-icon name="clipboard-document-list" class="w-12 h-12 text-border mb-3" />
                    <p class="text-sm font-semibold text-text-muted">Выберите тип теста</p>
                    <p class="text-xs text-text-muted mt-1">Настройки появятся здесь</p>
                </div>

                {{-- 3. ЕНТ конфигурация ────────────────────────────────────────── --}}
                <div x-show="accessType === 'ent'" x-cloak class="space-y-4">

                    {{-- Обязательные предметы --}}
                    <div class="card">
                        <p class="q-section-label mb-3">Обязательные предметы</p>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($mandatorySubjects as $s)
                                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-warning/10 border border-warning/30 rounded-xl text-sm font-semibold text-text">
                                    <x-icon name="shield-check" class="w-3.5 h-3.5 text-warning shrink-0" />
                                    {{ $s->title }}
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Выборные предметы --}}
                    <div class="card">
                        <div class="flex items-center justify-between mb-3">
                            <p class="q-section-label !mb-0">Выборные предметы (2)</p>
                            <label class="inline-flex items-center gap-2.5 cursor-pointer">
                                <span class="text-xs font-semibold text-text-muted select-none">Студент выбирает сам</span>
                                <div class="relative shrink-0">
                                    <input type="hidden" name="student_chooses_subject" value="0">
                                    <input type="checkbox" name="student_chooses_subject" value="1"
                                           class="sr-only peer" x-model="scs">
                                    <div class="w-11 h-6 bg-gray-200 peer-checked:bg-primary rounded-full transition-all shadow-inner border border-gray-200 peer-focus:ring-2 peer-focus:ring-primary/30"></div>
                                    <div class="absolute top-[2px] left-[2px] bg-white rounded-full h-5 w-5 shadow-md transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] peer-checked:translate-x-full"></div>
                                </div>
                            </label>
                        </div>

                        <div x-show="!scs" class="grid grid-cols-2 gap-4">
                            <template x-for="(e, i) in electives" :key="i">
                                <div>
                                    <label class="block text-sm font-semibold text-text mb-1.5"
                                           x-text="`Предмет ${i + 1}`"></label>
                                    <select :name="`elective[${i}][subject_id]`"
                                            x-model.number="e.subjectId"
                                            class="w-full px-4 py-2.5 border border-border rounded-2xl text-sm bg-white focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors">
                                        <option value="">— Не выбран —</option>
                                        <template x-for="(s, id) in electiveSubjects" :key="id">
                                            <option :value="id" :selected="e.subjectId == id" x-text="s.title"></option>
                                        </template>
                                    </select>
                                </div>
                            </template>
                        </div>

                        <div x-show="scs" x-cloak class="text-sm text-text-muted italic">
                            Студент самостоятельно выберет 2 предмета при прохождении теста.
                        </div>
                    </div>

                    {{-- Нұсқа --}}
                    <div class="card">
                        <p class="q-section-label mb-3">Нұсқа</p>
                        <input type="hidden" name="nusqa_mode" :value="nusqaMode">

                        <div class="flex flex-wrap gap-2 mb-4">
                            <button type="button" @click="nusqaMode = 'random'; nusqaNumber = ''"
                                :class="nusqaMode === 'random' ? 'btn btn-primary btn-sm' : 'btn btn-outline btn-sm text-text-muted'">
                                Случайные вопросы
                            </button>
                            <button type="button" @click="nusqaMode = 'student'; nusqaNumber = ''"
                                :class="nusqaMode === 'student' ? 'btn btn-info btn-sm' : 'btn btn-outline btn-sm text-text-muted'">
                                Студент выбирает нұсқа
                            </button>
                            <button type="button" @click="nusqaMode = 'specific'"
                                :class="nusqaMode === 'specific' ? 'btn btn-info btn-sm' : 'btn btn-outline btn-sm text-text-muted'">
                                Конкретная нұсқа
                            </button>
                        </div>

                        <div x-show="nusqaMode === 'specific'" x-cloak>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="form-group !mb-0 w-36">
                                    <label>Номер нұсқа <span class="text-danger">*</span></label>
                                    <input type="number" name="nusqa_number" x-model="nusqaNumber"
                                           min="1" max="9999" placeholder=""
                                           class="text-center font-mono text-lg font-bold">
                                </div>
                                <div class="mt-5">
                                    <template x-if="nusqaNumber && nusqaAllFound">
                                        <span class="inline-flex items-center gap-1.5 text-success text-sm font-semibold">
                                            <x-icon name="check-circle" class="w-4 h-4" />
                                            Нұсқа найдена во всех предметах
                                        </span>
                                    </template>
                                    <template x-if="nusqaNumber && !nusqaAllFound && nusqaValidation.length > 0">
                                        <span class="inline-flex items-center gap-1.5 text-danger text-sm font-semibold">
                                            <x-icon name="exclamation-circle" class="w-4 h-4" />
                                            Не все предметы имеют эту нұсқа
                                        </span>
                                    </template>
                                </div>
                            </div>
                            @error('nusqa_number')
                                <p class="form-error mb-3">{{ $message }}</p>
                            @enderror

                            <template x-if="nusqaValidation.length > 0">
                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                                    <template x-for="item in nusqaValidation" :key="item.id">
                                        <div :class="item.count !== null
                                                ? 'flex items-center justify-between px-3 py-2 rounded-xl bg-success/10 border border-success/30'
                                                : 'flex items-center justify-between px-3 py-2 rounded-xl bg-danger/10 border border-danger/30'">
                                            <span class="text-xs font-semibold text-text truncate mr-2" x-text="item.title"></span>
                                            <span x-show="item.count !== null"
                                                  class="text-xs font-mono font-bold text-success whitespace-nowrap"
                                                  x-text="`${item.count} вопр.`"></span>
                                            <span x-show="item.count === null"
                                                  class="text-xs font-bold text-danger">—</span>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <div x-show="nusqaMode === 'random'" x-cloak
                             class="text-sm text-text-muted italic">
                            Вопросы будут выбраны случайно из всех нұсқа.
                        </div>
                        <div x-show="nusqaMode === 'student'" x-cloak
                             class="text-sm text-text-muted italic">
                            Студент сам выберет нұсқа перед началом теста.
                        </div>
                    </div>
                </div>

                {{-- 4. Предмет конфигурация ───────────────────────────────────── --}}
                <div x-show="accessType === 'subject'" x-cloak class="space-y-4">
                    <div class="card">
                        <p class="q-section-label mb-3">Предмет</p>
                        <div class="form-group mb-0">
                            <label>Предмет <span class="text-danger">*</span></label>
                            <select name="subject[subject_id]"
                                    x-model.number="single.subjectId"
                                    @change="single.partType=''; single.partId=null; single.scp=false"
                                    class="w-full px-4 py-2.5 border border-border rounded-2xl text-sm bg-white focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors">
                                <option value="">— Выберите предмет —</option>
                                @foreach ($subjectsWithParts as $id => $s)
                                    <option value="{{ $id }}"
                                        @selected((int) old('subject.subject_id', $existingSingle?->subject_id) === (int) $id)>
                                        {{ $s['title'] }}
                                    </option>
                                @endforeach
                            </select>
                            @error('subject.subject_id') <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="card" x-show="single.subjectId" x-cloak>
                        <p class="q-section-label mb-3">Тип вопросов</p>
                        <input type="hidden" name="subject[part_type]" :value="single.partType">

                        <div class="flex flex-wrap gap-2 mb-4">
                            <button type="button"
                                @click="single.partType=''; single.partId=null; single.scp=false"
                                :class="single.partType===''
                                    ? 'btn btn-primary btn-sm'
                                    : 'btn btn-outline btn-sm text-text-muted'">
                                Случайно
                            </button>
                            @foreach ($partTypes as $val => $label)
                            <button type="button"
                                @click="single.partType='{{ $val }}'; single.partId=null; single.scp=false"
                                :class="single.partType==='{{ $val }}'
                                    ? 'btn btn-primary btn-sm'
                                    : 'btn btn-outline btn-sm text-text-muted'">
                                {{ $label }}
                            </button>
                            @endforeach
                        </div>

                        <div x-show="single.partType !== ''" x-cloak class="space-y-3">
                            {{-- Student chooses part --}}
                            <label class="inline-flex items-center gap-3 cursor-pointer">
                                <div class="relative shrink-0">
                                    <input type="hidden" name="subject[student_chooses_part]" value="0">
                                    <input type="checkbox" name="subject[student_chooses_part]" value="1"
                                           class="sr-only peer" x-model="single.scp"
                                           @change="if (single.scp) single.partId = null">
                                    <div class="w-9 h-5 bg-gray-200 peer-checked:bg-primary rounded-full transition-all shadow-inner border border-gray-200 peer-focus:ring-2 peer-focus:ring-primary/30"></div>
                                    <div class="absolute top-[2px] left-[2px] bg-white rounded-full h-4 w-4 shadow-md transition-all duration-300 ease-[cubic-bezier(0.34,1.56,0.64,1)] peer-checked:translate-x-full"></div>
                                </div>
                                <span class="text-sm font-semibold text-text select-none">Студент выбирает</span>
                            </label>

                            {{-- Specific part dropdown --}}
                            <div x-show="!single.scp" class="form-group !mb-0">
                                <label>
                                    Конкретный раздел
                                    <span class="text-xs text-text-muted font-normal">(необязательно — иначе случайно)</span>
                                </label>
                                <select name="subject[part_id]" x-model.number="single.partId"
                                        class="w-full px-4 py-2.5 border border-border rounded-2xl text-sm bg-white focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/20 transition-colors">
                                    <option value="">— Случайно из всех —</option>
                                    <template x-if="single.partType === 'nusqa'">
                                        <template x-for="n in subjectNusqas" :key="n.id">
                                            <option :value="n.id" :selected="single.partId == n.id"
                                                    x-text="`Нұсқа ${n.title} (${n.count} вопр.)`"></option>
                                        </template>
                                    </template>
                                    <template x-if="single.partType === 'topic'">
                                        <template x-for="t in subjectTopics" :key="t.id">
                                            <option :value="t.id" :selected="single.partId == t.id"
                                                    x-text="t.title"></option>
                                        </template>
                                    </template>
                                </select>
                            </div>
                            <input x-show="single.scp" type="hidden" name="subject[part_id]" value="">
                        </div>
                        <input x-show="single.partType === ''" type="hidden" name="subject[part_id]" value="">
                        <input x-show="single.partType === ''" type="hidden" name="subject[student_chooses_part]" value="0">
                    </div>
                </div>

            </div>
        </div>

        {{-- ── Actions ──────────────────────────────────────────────────────────── --}}
        <div class="flex items-center justify-between mt-5">
            <button type="submit" class="btn btn-success px-8" x-show="accessType !== ''" x-cloak>
                <x-icon name="check" class="w-4 h-4" />
                {{ $isEdit ? 'Сохранить изменения' : 'Открыть доступ' }}
            </button>
            <a href="{{ route('admin.test-accesses.index') }}" class="btn btn-outline ml-auto">Отмена</a>
        </div>

    </form>
</div>

{{-- Help modal trigger --}}
<button type="button"
        x-data
        @click="$dispatch('open-help-modal')"
        class="fixed bottom-6 right-6 w-10 h-10 rounded-full bg-primary text-white shadow-lg flex items-center justify-center hover:bg-primary/90 transition-colors z-40"
        title="Как работать с этой страницей">
    <x-icon name="information-circle" class="w-5 h-5" />
</button>

@include('admin.test-accesses._help-modal')

@endsection
