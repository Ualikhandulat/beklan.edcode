@php
    $d = $q->detail ?? null;
    $oldAnswers = array_map('intval', old('answers', $d?->answers ?? []));
    $letters = ['A','B','C','D','E','F','G','H'];
@endphp

<form method="POST" action="{{ $updateRoute ?? $storeRoutes['multi'] }}">
    @csrf
    @isset($updateRoute) @method('PUT') @endisset

    <p class="q-section-label">Текст вопроса</p>
    <div class="form-group">
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question', $d?->question ?? '') }}">
        <div data-wysiwyg="question" data-placeholder="Введите вопрос..."></div>
        @error('question') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    @if ($errors->has('answers'))
        <x-alert type="danger" :message="$errors->first('answers')" />
    @endif

    <p class="q-section-label mt-6">
        Варианты ответов
        <span class="normal-case font-semibold text-text-muted tracking-normal ml-1">— отметьте правильные (минимум 2)</span>
    </p>

    <div class="space-y-3">
        @foreach (range(1, 8) as $i)
            @php $checked = in_array($i, $oldAnswers); @endphp
            <div class="q-var-row">
                <label class="mt-1 cursor-pointer">
                    <input type="checkbox" name="answers[]" value="{{ $i }}"
                           {{ $checked ? 'checked' : '' }}
                           class="sr-only peer">
                    <div class="q-var-letter peer-checked:bg-success peer-checked:text-white {{ $checked ? 'bg-success text-white' : 'bg-gray-100 text-text-muted' }} transition-colors">
                        {{ $letters[$i - 1] }}
                    </div>
                </label>
                <div class="flex-1">
                    <input type="hidden" name="var{{ $i }}" data-wysiwyg-target="var{{ $i }}" value="{{ old("var{$i}", $d?->{"var{$i}"} ?? '') }}">
                    <div data-wysiwyg="var{{ $i }}" data-wysiwyg-plain
                         data-placeholder="Вариант {{ $letters[$i - 1] }}{{ $i > 6 ? ' (необязательно)' : '' }}"></div>
                    @error("var{$i}") <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
        <button type="submit" class="btn btn-success">{{ isset($updateRoute) ? 'Сохранить изменения' : 'Добавить вопрос' }}</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
