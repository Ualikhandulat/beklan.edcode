@use(App\Enums\QuestionType)

<tr class="cursor-pointer" onclick="window.location='{{ $editRoute }}'">

    <td class="text-text-muted text-xs font-mono w-10 hidden sm:table-cell">
        {{ $index }}
    </td>

    <td class="w-36">
        <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold"
              style="background-color:color-mix(in srgb,{{ $question->type->color() }} 13%,white);color:{{ $question->type->color() }}">
            <x-icon :name="'question-' . $question->type->value" class="w-3 h-3 shrink-0" />
            {{ $question->type->title() }}
        </span>
    </td>

    <td class="max-w-xs">
        @if ($question->type === QuestionType::IS_GROUP)
            <span class="text-sm text-text-muted">
                {{ Str::limit(strip_tags($question->text ?? '—'), 100) }}
            </span>
        @else
            <span class="text-sm text-text">
                {{ Str::limit(strip_tags($question->detail?->question ?? '—'), 120) }}
            </span>
        @endif
    </td>

    <td class="w-20 text-xs text-text-muted text-center">
        @if ($question->type !== QuestionType::IS_GROUP)
            {{ $question->count_variants }}&thinsp;/&thinsp;{{ $question->count_answers }}
        @else
            —
        @endif
    </td>

    <td class="w-20" onclick="event.stopPropagation()">
        <div class="flex items-center gap-1">
            <x-btn.edit :route="$editRoute" />
            <x-btn.delete :route="$destroyRoute" />
        </div>
    </td>

</tr>

{{-- Questions for IS_GROUP context --}}
@if ($question->type === QuestionType::IS_GROUP)
    @foreach ($question->details as $j => $detail)
        @php
            $detailEditRoute    = route('admin.subjects.parts.questions.details.edit',    [$subject, $part, $question, $detail]);
            $detailDestroyRoute = route('admin.subjects.parts.questions.details.destroy', [$subject, $part, $question, $detail]);
            $varCount = collect(range(1, 5))->filter(fn($v) => !empty($detail->{"var{$v}"}))->count();
            $ansCount = count($detail->answers ?? []);
        @endphp
        <tr class="cursor-pointer bg-gray-50" onclick="window.location='{{ $detailEditRoute }}'">

            <td class="text-text-muted text-xs font-mono hidden sm:table-cell">
                <span class="pl-3 border-l-2 border-primary/40 block">
                    {{ $detail->id }}
                </span>
            </td>

            <td>
                <span class="text-[11px] font-semibold text-text-muted">вопрос</span>
            </td>

            <td class="max-w-xs">
                <span class="text-sm text-text">
                    {{ Str::limit(strip_tags($detail->question ?? '—'), 120) }}
                </span>
            </td>

            <td class="text-xs text-text-muted text-center">
                {{ $varCount }}&thinsp;/&thinsp;{{ $ansCount }}
            </td>

            <td onclick="event.stopPropagation()">
                <div class="flex items-center gap-1">
                    <x-btn.edit :route="$detailEditRoute" />
                    <x-btn.delete :route="$detailDestroyRoute" />
                </div>
            </td>

        </tr>
    @endforeach
@endif
