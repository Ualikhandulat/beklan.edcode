{{-- Sidebar for detail/review page --}}
<div class="p-3">

    {{-- Subject tabs --}}
    <div class="flex flex-wrap gap-1.5 px-2 mb-3">
        <template x-for="(subject, si) in subjects" :key="si">
            <button type="button"
                    @click="activeSubject = si; activeQuestion = 0"
                    class="px-2.5 py-1 rounded-lg text-xs font-extrabold transition-all duration-150 border"
                    :class="si === activeSubject
                        ? 'border-primary bg-primary text-white shadow-sm'
                        : 'border-border bg-white text-text-muted hover:border-primary/40 hover:text-text'">
                <span x-text="subject.subject.title"></span>
            </button>
        </template>
    </div>

    {{-- Current subject info --}}
    <div class="flex items-center justify-between px-2 py-1.5 mb-2">
        <p class="text-xs font-extrabold uppercase tracking-widest text-primary truncate flex-1"
           x-text="currentSubject?.subject.title"></p>
        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full ml-2 shrink-0"
              :class="
                  subjects[activeSubject]?.questions.filter(q => q.is_right).length === subjects[activeSubject]?.questions.length
                      ? 'bg-success/15 text-success'
                      : 'bg-danger/15 text-danger'
              "
              x-text="`${subjects[activeSubject]?.questions.filter(q => q.is_right).length}/${subjects[activeSubject]?.questions.length}`">
        </span>
    </div>

    {{-- Question pills --}}
    <div class="flex flex-wrap gap-1.5 px-2">
        <template x-for="(q, qi) in currentSubject?.questions ?? []" :key="qi">
            <button type="button"
                    @click="goTo(activeSubject, qi)"
                    class="w-8 h-8 rounded-lg text-xs font-extrabold transition-all duration-150"
                    :class="
                        qi === activeQuestion
                            ? 'bg-primary text-white shadow-sm scale-110'
                            : (q.is_right
                                ? 'bg-success/20 text-success border border-success/30 hover:bg-success/30'
                                : 'bg-danger/15 text-danger border border-danger/20 hover:bg-danger/25')
                    "
                    x-text="qi + 1">
            </button>
        </template>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-4 px-2 mt-3 pt-3 border-t border-border">
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 rounded-md bg-success/20 border border-success/30"></div>
            <span class="text-[10px] font-bold text-text-muted">Верно</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 rounded-md bg-danger/15 border border-danger/20"></div>
            <span class="text-[10px] font-bold text-text-muted">Ошибка</span>
        </div>
    </div>
</div>
