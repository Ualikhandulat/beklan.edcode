<form method="POST" action="{{ $storeRoutes['match'] }}">
    @csrf

    <div class="form-group">
        <label>Текст вопроса (необязательно)</label>
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question') }}">
        <div data-wysiwyg="question" data-placeholder="Необязательный вводный текст..."></div>
        @error('question') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-5 mb-4">
        <div>
            <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Левый столбец</p>
            <x-form.input name="var1" label="Элемент 1" placeholder="Левый 1" :required="true" />
            <x-form.input name="var2" label="Элемент 2" placeholder="Левый 2" :required="true" />
        </div>
        <div>
            <p class="text-xs font-extrabold text-success uppercase tracking-widest mb-3">Правый столбец (правильные)</p>
            <div class="form-group">
                <label class="text-success">Ответ для элемента 1 <span class="text-danger">*</span></label>
                <input type="text" name="var5" value="{{ old('var5') }}"
                       placeholder="Правильный для 1-го"
                       class="border-success/50 focus:border-success focus:ring-success/20" required>
                @error('var5') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="text-success">Ответ для элемента 2 <span class="text-danger">*</span></label>
                <input type="text" name="var6" value="{{ old('var6') }}"
                       placeholder="Правильный для 2-го"
                       class="border-success/50 focus:border-success focus:ring-success/20" required>
                @error('var6') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Дистракторы</p>
    <div class="grid grid-cols-2 gap-5">
        <x-form.input name="var7" label="Дистрактор 1" placeholder="Лишний вариант" :required="true" />
        <x-form.input name="var8" label="Дистрактор 2 (необязательно)" placeholder="Лишний вариант" />
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-success">Добавить вопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
