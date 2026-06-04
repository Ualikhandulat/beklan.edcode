@php $oldAnswers = array_map('intval', old('answers', [])); @endphp

<form method="POST" action="{{ $storeRoutes['multi'] }}">
    @csrf

    <x-form.textarea name="question" label="Текст вопроса" :rows="3"
        placeholder="Введите вопрос..." :required="true" />

    @if ($errors->has('answers'))
        <x-alert type="danger" :message="$errors->first('answers')" />
    @endif

    <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">
        Варианты — отметьте правильные (минимум 2) <span class="text-danger">*</span>
    </p>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5">
        @foreach (range(1, 8) as $i)
            <div class="form-group flex items-start gap-3">
                <label class="inline-flex items-center gap-2 cursor-pointer mt-2 mb-0 shrink-0">
                    <input type="checkbox" name="answers[]" value="{{ $i }}"
                           {{ in_array($i, $oldAnswers) ? 'checked' : '' }}
                           class="w-4 h-4 rounded accent-primary mt-0.5">
                    <span class="text-xs font-bold text-text-muted">{{ $i }}</span>
                </label>
                <div class="flex-1">
                    <input type="text" name="var{{ $i }}"
                           value="{{ old("var{$i}") }}"
                           placeholder="Вариант {{ $i }}{{ $i > 6 ? ' (необязательно)' : '' }}"
                           {{ $i <= 6 ? 'required' : '' }}>
                    @error("var{$i}") <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary">Добавить вопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
