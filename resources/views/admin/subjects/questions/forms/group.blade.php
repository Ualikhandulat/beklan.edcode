@php
    $contextOptions = $contexts->mapWithKeys(fn($q) => [
        $q->id => Str::limit(strip_tags($q->text), 80)
    ])->toArray();
    $hasContexts  = $contexts->isNotEmpty();
    $defaultMode  = $hasContexts ? 'existing' : 'new';
@endphp

<form method="POST" action="{{ $storeRoutes['group'] }}" x-data="{ mode: '{{ $defaultMode }}' }">
    @csrf

    <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Контекст</p>
    <div class="flex gap-2 mb-5">
        <button type="button" @click="mode = 'new'"
                :class="mode === 'new' ? 'btn-primary' : 'btn-outline'"
                class="btn btn-sm">Новый контекст</button>
        @if ($hasContexts)
            <button type="button" @click="mode = 'existing'"
                    :class="mode === 'existing' ? 'btn-primary' : 'btn-outline'"
                    class="btn btn-sm">Существующий ({{ $contexts->count() }})</button>
        @endif
    </div>

    <input type="hidden" name="context_mode" x-model="mode">

    <div x-show="mode === 'new'">
        <x-form.textarea name="context_text" label="Текст контекста" :rows="5"
            placeholder="Прочитайте текст и ответьте на вопросы..." />
    </div>

    @if ($hasContexts)
        <div x-show="mode === 'existing'" x-cloak>
            <x-form.select name="context_id" label="Выберите контекст"
                :options="$contextOptions" placeholder="Выберите контекст..." :searchable="true" />
        </div>
    @endif

    <hr class="border-border my-5">

    <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Подвопрос</p>

    <x-form.textarea name="question" label="Текст подвопроса" :rows="2"
        placeholder="Вопрос к контексту..." :required="true" />

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5">
        <div class="form-group">
            <label class="text-success">Правильный ответ <span class="text-danger">*</span></label>
            <input type="text" name="var1" value="{{ old('var1') }}"
                   placeholder="Правильный ответ"
                   class="border-success/50 focus:border-success focus:ring-success/20" required>
            @error('var1') <p class="form-error">{{ $message }}</p> @enderror
        </div>
        <x-form.input name="var2" label="Вариант 2" placeholder="Неправильный вариант" :required="true" />
        <x-form.input name="var3" label="Вариант 3" placeholder="Неправильный вариант" :required="true" />
        <x-form.input name="var4" label="Вариант 4 (необязательно)" placeholder="Доп. вариант" />
        <x-form.input name="var5" label="Вариант 5 (необязательно)" placeholder="Доп. вариант" />
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-primary">Добавить подвопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
