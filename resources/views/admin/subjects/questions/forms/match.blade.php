<form method="POST" action="{{ $storeRoutes['match'] }}">
    @csrf

    <p class="q-section-label">Текст вопроса <span class="text-danger ml-0.5">*</span></p>
    <div class="form-group">
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question') }}">
        <div data-wysiwyg="question" data-placeholder="Введите текст вопроса..."></div>
        @error('question') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-2 gap-6 mt-6">

        <div>
            <p class="q-section-label">Левый столбец</p>
            <div class="space-y-3">
                @foreach ([[1,'1-й элемент'],[2,'2-й элемент']] as [$n,$label])
                    <div class="q-var-row">
                        <div class="q-var-letter q-var-letter-wrong">{{ $n }}</div>
                        <div class="flex-1">
                            <input type="hidden" name="var{{ $n }}" data-wysiwyg-target="var{{ $n }}" value="{{ old("var{$n}") }}">
                            <div data-wysiwyg="var{{ $n }}" data-wysiwyg-plain data-placeholder="{{ $label }}"></div>
                            @error("var{$n}") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            <p class="q-section-label text-success">Правый столбец <span class="normal-case font-semibold tracking-normal text-text-muted ml-1">(правильные)</span></p>
            <div class="space-y-3">
                @foreach ([[5,'Ответ для 1-го'],[6,'Ответ для 2-го']] as [$n,$label])
                    <div class="q-var-row">
                        <div class="q-var-letter q-var-letter-correct">{{ $n - 4 }}</div>
                        <div class="flex-1">
                            <input type="hidden" name="var{{ $n }}" data-wysiwyg-target="var{{ $n }}" value="{{ old("var{$n}") }}">
                            <div data-wysiwyg="var{{ $n }}" data-wysiwyg-plain data-placeholder="{{ $label }}"></div>
                            @error("var{$n}") <p class="form-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <p class="q-section-label mt-6">Дистракторы <span class="normal-case font-semibold tracking-normal text-text-muted ml-1">(лишние варианты)</span></p>
    <div class="space-y-3">
        @foreach ([[7,'Лишний 1'],[8,'Лишний 2 (необязательно)']] as [$n,$label])
            <div class="q-var-row">
                <div class="q-var-letter q-var-letter-opt">×</div>
                <div class="flex-1">
                    <input type="hidden" name="var{{ $n }}" data-wysiwyg-target="var{{ $n }}" value="{{ old("var{$n}") }}">
                    <div data-wysiwyg="var{{ $n }}" data-wysiwyg-plain data-placeholder="{{ $label }}"></div>
                    @error("var{$n}") <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
        <button type="submit" class="btn btn-success">Сохранить вопрос</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
