<form method="POST" action="{{ $storeRoutes['one'] }}">
    @csrf

    <div class="form-group">
        <label>Текст вопроса <span class="text-danger">*</span></label>
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question') }}">
        <div data-wysiwyg="question" data-placeholder="Введите вопрос..."></div>
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
        <button type="submit" class="btn btn-success">Добавить вопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
