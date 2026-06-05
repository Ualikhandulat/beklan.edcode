@php
    $contextOptions = $contexts->mapWithKeys(fn($q) => [
        $q->id => Str::limit(strip_tags($q->text), 80)
    ])->toArray();
    $hasContexts = $contexts->isNotEmpty();
    $defaultMode = $hasContexts ? 'existing' : 'new';
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
        <div class="form-group">
            <label>Текст контекста</label>
            <input type="hidden" name="context_text" data-wysiwyg-target="context_text" value="{{ old('context_text') }}">
            <div data-wysiwyg="context_text" data-wysiwyg-context data-placeholder="Прочитайте текст и ответьте на вопросы..."></div>
            @error('context_text') <p class="form-error">{{ $message }}</p> @enderror
        </div>
    </div>

    @if ($hasContexts)
        <div x-show="mode === 'existing'" x-cloak>
            <x-form.select name="context_id" label="Выберите контекст"
                :options="$contextOptions" placeholder="Выберите контекст..." :searchable="true" />
        </div>
    @endif

    <hr class="border-border my-5">

    <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Подвопрос</p>

    <div class="form-group">
        <label>Текст подвопроса <span class="text-danger">*</span></label>
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question') }}">
        <div data-wysiwyg="question" data-placeholder="Вопрос к контексту..."></div>
        @error('question') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
        <label class="text-success">Правильный ответ <span class="text-danger">*</span></label>
        <input type="hidden" name="var1" data-wysiwyg-target="var1" value="{{ old('var1') }}">
        <div data-wysiwyg="var1" data-wysiwyg-plain data-placeholder="Правильный ответ"></div>
        @error('var1') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group">
        <label>Вариант 2 <span class="text-danger">*</span></label>
        <input type="hidden" name="var2" data-wysiwyg-target="var2" value="{{ old('var2') }}">
        <div data-wysiwyg="var2" data-wysiwyg-plain data-placeholder="Неправильный вариант"></div>
        @error('var2') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group">
        <label>Вариант 3 <span class="text-danger">*</span></label>
        <input type="hidden" name="var3" data-wysiwyg-target="var3" value="{{ old('var3') }}">
        <div data-wysiwyg="var3" data-wysiwyg-plain data-placeholder="Неправильный вариант"></div>
        @error('var3') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group">
        <label>Вариант 4 <span class="text-text-muted text-xs font-normal">(необязательно)</span></label>
        <input type="hidden" name="var4" data-wysiwyg-target="var4" value="{{ old('var4') }}">
        <div data-wysiwyg="var4" data-wysiwyg-plain data-placeholder="Доп. вариант"></div>
        @error('var4') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group">
        <label>Вариант 5 <span class="text-text-muted text-xs font-normal">(необязательно)</span></label>
        <input type="hidden" name="var5" data-wysiwyg-target="var5" value="{{ old('var5') }}">
        <div data-wysiwyg="var5" data-wysiwyg-plain data-placeholder="Доп. вариант"></div>
        @error('var5') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-success">Добавить подвопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
