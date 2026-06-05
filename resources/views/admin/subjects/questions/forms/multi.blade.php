@php $oldAnswers = array_map('intval', old('answers', [])); @endphp

<form method="POST" action="{{ $storeRoutes['multi'] }}">
    @csrf

    <div class="form-group">
        <label>Текст вопроса <span class="text-danger">*</span></label>
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question') }}">
        <div data-wysiwyg="question" data-placeholder="Введите вопрос..."></div>
        @error('question') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    @if ($errors->has('answers'))
        <x-alert type="danger" :message="$errors->first('answers')" />
    @endif

    <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">
        Варианты — отметьте правильные (минимум 2) <span class="text-danger">*</span>
    </p>

    @foreach (range(1, 8) as $i)
        <div class="form-group flex items-center gap-3">
            <input type="checkbox" name="answers[]" value="{{ $i }}"
                   {{ in_array($i, $oldAnswers) ? 'checked' : '' }}
                   class="w-4 h-4 rounded accent-primary shrink-0">
            <div class="flex-1">
                <input type="hidden" name="var{{ $i }}" data-wysiwyg-target="var{{ $i }}" value="{{ old("var{$i}") }}">
                <div data-wysiwyg="var{{ $i }}" data-wysiwyg-plain
                     data-placeholder="Вариант {{ $i }}{{ $i > 6 ? ' (необязательно)' : '' }}"></div>
                @error("var{$i}") <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
    @endforeach

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-success">Добавить вопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
