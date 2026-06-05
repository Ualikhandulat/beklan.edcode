@use(App\Enums\QuestionType)

@php $letters = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H']; @endphp

<div class="q-card group">
    <div class="q-card-accent" style="background-color: {{ $question->type->color() }}"></div>
    <div class="q-card-body">

        {{-- Header --}}
        <div class="flex items-start justify-between gap-3 mb-3">
            <div class="flex items-center gap-3">
                <span class="q-num">#{{ str_pad($index, 2, '0', STR_PAD_LEFT) }}</span>
                <span class="badge" style="background-color:color-mix(in srgb,{{ $question->type->color() }} 12%,white);color:{{ $question->type->color() }}">
                    <x-icon :name="'question-' . $question->type->value" class="w-3 h-3 inline-block mr-0.5" />{{ $question->type->title() }}
                </span>
            </div>
            <form method="POST" action="{{ $destroyRoute }}"
                  onsubmit="return confirm('Удалить?')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light opacity-0 group-hover:opacity-100 transition-opacity shrink-0">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            </form>
        </div>

        @if ($question->type === QuestionType::IS_GROUP)
            {{-- Context block --}}
            @if ($question->text)
                <p class="q-preview mb-3 line-clamp-2 text-text-muted text-xs">
                    {{ Str::limit(strip_tags($question->text), 180) }}
                </p>
            @endif

            @forelse ($question->details as $j => $detail)
                <div class="mt-3 pl-4 border-l-2 border-primary/25 first:mt-0">
                    <p class="text-[10px] font-extrabold text-text-muted uppercase tracking-wider mb-1">
                        {{ $index }}.{{ $j + 1 }}
                    </p>
                    <p class="q-preview mb-2">
                        {{ Str::limit(strip_tags($detail->question ?? ''), 120) }}
                    </p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach (range(1, 5) as $v)
                            @php $var = $detail->{"var{$v}"}; @endphp
                            @if ($var)
                                <span class="q-chip {{ in_array($v, $detail->answers ?? []) ? 'q-chip-correct' : 'q-chip-wrong' }}">
                                    {{ in_array($v, $detail->answers ?? []) ? '✓ ' : '' }}{{ $letters[$v - 1] }}. {{ Str::limit(strip_tags($var), 50) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                </div>
            @empty
                <p class="text-xs text-text-muted italic">Подвопросы не добавлены</p>
            @endforelse

        @else
            {{-- Regular question --}}
            <p class="q-preview mb-3">
                {{ Str::limit(strip_tags($question->detail?->question ?? '—'), 200) }}
            </p>

            @if ($question->detail)
                @if ($question->type === QuestionType::IS_MATCH)
                    {{-- Match: show pairs --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ([[1,5],[2,6]] as [$l,$r])
                            @php $lv = $question->detail->{"var{$l}"}; $rv = $question->detail->{"var{$r}"}; @endphp
                            @if ($lv && $rv)
                                <span class="q-chip bg-success-light text-success">
                                    {{ Str::limit(strip_tags($lv), 30) }} → {{ Str::limit(strip_tags($rv), 30) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @else
                    {{-- One / Multi: letter chips --}}
                    <div class="flex flex-wrap gap-1.5">
                        @foreach (range(1, 8) as $v)
                            @php $var = $question->detail->{"var{$v}"}; @endphp
                            @if ($var)
                                <span class="q-chip {{ in_array($v, $question->detail->answers ?? []) ? 'q-chip-correct' : 'q-chip-wrong' }}">
                                    @if (in_array($v, $question->detail->answers ?? []))✓ @endif{{ $letters[$v - 1] }}. {{ Str::limit(strip_tags($var), 45) }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            @endif
        @endif

    </div>
</div>
