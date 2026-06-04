<form method="POST" action="{{ $storeRoutes['one'] }}">
    @csrf

    <x-form.textarea name="question" label="Текст вопроса" :rows="3"
        placeholder="Введите вопрос..." :required="true" />

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
        <button type="submit" class="btn btn-primary">Добавить вопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
