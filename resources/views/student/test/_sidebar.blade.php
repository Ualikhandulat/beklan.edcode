{{-- Question navigation sidebar --}}
<div class="p-3">

    {{-- Subject tabs --}}
    @if (count($subjectsData) > 1)
        <div class="flex flex-wrap gap-1.5 px-2 mb-3">
            <template x-for="(subject, si) in subjects" :key="si">
                <button type="button"
                        @click="activeSubject = si; activeQuestion = 0"
                        class="px-2.5 py-1 rounded-lg text-xs font-extrabold transition-all duration-150 border max-w-[9rem] truncate"
                        :class="si === activeSubject
                            ? 'border-primary bg-primary text-white shadow-sm'
                            : 'border-border bg-white text-text-muted hover:border-primary/40 hover:text-text'">
                    <span x-text="subject.subject.title" class="truncate"></span>
                </button>
            </template>
        </div>
    @endif

    {{-- Current subject info --}}
    <div class="flex items-center justify-between px-2 py-1.5 mb-2">
        <p class="text-xs font-extrabold uppercase tracking-widest text-primary truncate flex-1"
           x-text="currentSubject?.subject.title"></p>
        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-2 shrink-0"
              :class="currentSubject?.answered === currentSubject?.questions.length
                  ? 'bg-success/15 text-success'
                  : (currentSubject?.answered > 0 ? 'bg-primary/15 text-primary' : 'bg-gray-100 text-text-muted')"
              x-text="`${currentSubject?.answered ?? 0}/${currentSubject?.questions.length ?? 0}`">
        </span>
    </div>

    {{-- Question pills for current subject only --}}
    <div class="flex flex-wrap gap-1.5 px-2">
        <template x-for="(q, qi) in currentSubject?.questions ?? []" :key="qi">
            <button type="button"
                    @click="goTo(activeSubject, qi)"
                    class="w-8 h-8 rounded-lg text-xs font-extrabold transition-all duration-150"
                    :class="
                        qi === activeQuestion
                            ? 'bg-primary text-white shadow-sm scale-110'
                            : (isAnswered(q)
                                ? 'bg-success/20 text-success border border-success/30 hover:bg-success/30'
                                : 'bg-gray-100 text-text-muted hover:bg-gray-200')
                    "
                    x-text="qi + 1">
            </button>
        </template>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 px-2 mt-3 pt-3 border-t border-border">
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 rounded-md bg-success/20 border border-success/30"></div>
            <span class="text-[10px] font-bold text-text-muted">{{ __('Отвечен') }}</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 rounded-md bg-gray-100"></div>
            <span class="text-[10px] font-bold text-text-muted">{{ __('Не отвечен') }}</span>
        </div>
    </div>
</div>
