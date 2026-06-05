<form method="POST" action="{{ $storeRoutes['match'] }}">
    @csrf

    <div class="form-group">
        <label>Текст вопроса <span class="text-text-muted text-xs font-normal">(необязательно)</span></label>
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question') }}">
        <div data-wysiwyg="question" data-placeholder="Необязательный вводный текст..."></div>
        @error('question') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-5 mb-4">
        <div>
            <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Левый столбец</p>
            <div class="form-group">
                <label>Элемент 1 <span class="text-danger">*</span></label>
                <input type="hidden" name="var1" data-wysiwyg-target="var1" value="{{ old('var1') }}">
                <div data-wysiwyg="var1" data-wysiwyg-plain data-placeholder="Левый 1"></div>
                @error('var1') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label>Элемент 2 <span class="text-danger">*</span></label>
                <input type="hidden" name="var2" data-wysiwyg-target="var2" value="{{ old('var2') }}">
                <div data-wysiwyg="var2" data-wysiwyg-plain data-placeholder="Левый 2"></div>
                @error('var2') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
        <div>
            <p class="text-xs font-extrabold text-success uppercase tracking-widest mb-3">Правый столбец (правильные)</p>
            <div class="form-group">
                <label class="text-success">Ответ для элемента 1 <span class="text-danger">*</span></label>
                <input type="hidden" name="var5" data-wysiwyg-target="var5" value="{{ old('var5') }}">
                <div data-wysiwyg="var5" data-wysiwyg-plain data-placeholder="Правильный для 1-го"></div>
                @error('var5') <p class="form-error">{{ $message }}</p> @enderror
            </div>
            <div class="form-group">
                <label class="text-success">Ответ для элемента 2 <span class="text-danger">*</span></label>
                <input type="hidden" name="var6" data-wysiwyg-target="var6" value="{{ old('var6') }}">
                <div data-wysiwyg="var6" data-wysiwyg-plain data-placeholder="Правильный для 2-го"></div>
                @error('var6') <p class="form-error">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    <p class="text-xs font-extrabold text-text-muted uppercase tracking-widest mb-3">Дистракторы</p>
    <div class="form-group">
        <label>Дистрактор 1 <span class="text-danger">*</span></label>
        <input type="hidden" name="var7" data-wysiwyg-target="var7" value="{{ old('var7') }}">
        <div data-wysiwyg="var7" data-wysiwyg-plain data-placeholder="Лишний вариант"></div>
        @error('var7') <p class="form-error">{{ $message }}</p> @enderror
    </div>
    <div class="form-group">
        <label>Дистрактор 2 <span class="text-text-muted text-xs font-normal">(необязательно)</span></label>
        <input type="hidden" name="var8" data-wysiwyg-target="var8" value="{{ old('var8') }}">
        <div data-wysiwyg="var8" data-wysiwyg-plain data-placeholder="Лишний вариант"></div>
        @error('var8') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center justify-between mt-6">
        <button type="submit" class="btn btn-success">Добавить вопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
