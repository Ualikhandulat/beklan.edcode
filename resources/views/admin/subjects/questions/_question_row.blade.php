@use(App\Enums\QuestionType)

<div class="card card-sm">

    @if ($question->type === QuestionType::IS_GROUP)
        {{-- Контекст + подвопросы --}}
        <div class="flex items-start justify-between gap-3 mb-4">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <span class="badge {{ $question->type->badgeClass() }} shrink-0 mt-0.5">{{ $question->type->label() }}</span>
                <p class="text-sm font-semibold text-text">{{ $question->text }}</p>
            </div>
            <form method="POST" action="{{ $destroyRoute }}"
                  onsubmit="return confirm('Удалить контекст и все его подвопросы?')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light shrink-0">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            </form>
        </div>

        @forelse ($question->details as $j => $detail)
            <div class="ml-4 pl-4 border-l-2 border-primary/30 mb-2 last:mb-0">
                <p class="text-xs font-extrabold text-text-muted mb-1">{{ $index }}.{{ $j + 1 }}</p>
                <p class="text-sm font-semibold text-text mb-2">{{ $detail->question }}</p>
                <div class="flex flex-wrap gap-1">
                    @foreach (range(1, 5) as $v)
                        @php $var = $detail->{"var{$v}"}; @endphp
                        @if ($var)
                            <span class="px-2 py-0.5 rounded-lg text-xs {{ in_array($v, $detail->answers ?? []) ? 'bg-success-light text-success font-bold' : 'bg-gray-100 text-text-muted' }}">
                                {{ Str::limit(strip_tags($var), 60) }}
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <p class="text-xs text-text-muted ml-4">Подвопросы не добавлены.</p>
        @endforelse

    @else
        {{-- Обычный вопрос --}}
        <div class="flex items-start justify-between gap-3">
            <div class="flex items-start gap-3 flex-1 min-w-0">
                <span class="text-xs font-extrabold text-text-muted w-7 shrink-0 mt-0.5">{{ $index }}</span>
                <span class="badge {{ $question->type->badgeClass() }} shrink-0 mt-0.5">{{ $question->type->label() }}</span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-text mb-2">{{ $question->detail?->question ?? '—' }}</p>
                    @if ($question->detail)
                        <div class="flex flex-wrap gap-1">
                            @foreach (range(1, 10) as $v)
                                @php $var = $question->detail->{"var{$v}"}; @endphp
                                @if ($var)
                                    <span class="px-2 py-0.5 rounded-lg text-xs {{ in_array($v, $question->detail->answers ?? []) ? 'bg-success-light text-success font-bold' : 'bg-gray-100 text-text-muted' }}">
                                        {{ Str::limit(strip_tags($var), 60) }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            <form method="POST" action="{{ $destroyRoute }}" onsubmit="return confirm('Удалить вопрос?')">
                @csrf @method('DELETE')
                <button class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light shrink-0">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            </form>
        </div>
    @endif

</div>
