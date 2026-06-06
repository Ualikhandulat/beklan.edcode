{{-- Question navigation sidebar --}}
<div class="p-3">
    <template x-for="(subject, si) in subjects" :key="si">
        <div class="mb-3">

            {{-- Subject label --}}
            <div class="flex items-center gap-2 px-2 py-1.5 mb-2">
                <div class="w-1.5 h-1.5 rounded-full shrink-0"
                     :style="si === activeSubject ? 'background: var(--color-primary)' : 'background: var(--color-border)'"></div>
                <p class="text-xs font-extrabold uppercase tracking-widest truncate flex-1"
                   :class="si === activeSubject ? 'text-primary' : 'text-text-muted'"
                   x-text="subject.subject.title"></p>
                <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full"
                      :class="subject.answered === subject.questions.length
                          ? 'bg-success/15 text-success'
                          : (subject.answered > 0 ? 'bg-primary/15 text-primary' : 'bg-gray-100 text-text-muted')"
                      x-text="`${subject.answered}/${subject.questions.length}`"></span>
            </div>

            {{-- Question pills grid --}}
            <div class="flex flex-wrap gap-1.5 px-2">
                <template x-for="(q, qi) in subject.questions" :key="qi">
                    <button type="button"
                            @click="goTo(si, qi)"
                            class="w-8 h-8 rounded-lg text-xs font-extrabold transition-all duration-150"
                            :class="
                                si === activeSubject && qi === activeQuestion
                                    ? 'bg-primary text-white shadow-sm scale-110'
                                    : (isAnswered(q)
                                        ? 'bg-success/20 text-success border border-success/30 hover:bg-success/30'
                                        : 'bg-gray-100 text-text-muted hover:bg-gray-200')
                            "
                            x-text="qi + 1">
                    </button>
                </template>
            </div>
        </div>
    </template>

    {{-- Legend --}}
    <div class="flex items-center gap-4 px-2 mt-3 pt-3 border-t border-border">
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 rounded-md bg-success/20 border border-success/30"></div>
            <span class="text-[10px] font-bold text-text-muted">Отвечен</span>
        </div>
        <div class="flex items-center gap-1.5">
            <div class="w-4 h-4 rounded-md bg-gray-100"></div>
            <span class="text-[10px] font-bold text-text-muted">Не отвечен</span>
        </div>
    </div>
</div>
