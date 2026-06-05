@php $d = $q->detail ?? null; @endphp

<form method="POST" action="{{ $updateRoute ?? $storeRoutes['one'] }}">
    @csrf
    @isset($updateRoute) @method('PUT') @endisset

    <p class="q-section-label">Текст вопроса</p>
    <div class="form-group">
        <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question', $d?->question ?? '') }}">
        <div data-wysiwyg="question" data-placeholder="Введите вопрос..."></div>
        @error('question') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    <p class="q-section-label mt-6">Варианты ответов</p>

    @php
        $variants = [
            ['key' => 'var1', 'letter' => 'A', 'correct' => true,  'required' => true,  'placeholder' => 'Правильный ответ'],
            ['key' => 'var2', 'letter' => 'B', 'correct' => false, 'required' => true,  'placeholder' => 'Неправильный вариант'],
            ['key' => 'var3', 'letter' => 'C', 'correct' => false, 'required' => true,  'placeholder' => 'Неправильный вариант'],
            ['key' => 'var4', 'letter' => 'D', 'correct' => false, 'required' => false, 'placeholder' => 'Доп. вариант (необязательно)'],
            ['key' => 'var5', 'letter' => 'E', 'correct' => false, 'required' => false, 'placeholder' => 'Доп. вариант (необязательно)'],
        ];
    @endphp

    <div class="space-y-3">
        @foreach ($variants as $v)
            <div class="q-var-row">
                <div class="q-var-letter {{ $v['correct'] ? 'q-var-letter-correct' : ($v['required'] ? 'q-var-letter-wrong' : 'q-var-letter-opt') }}">
                    {{ $v['letter'] }}
                </div>
                <div class="flex-1">
                    <input type="hidden" name="{{ $v['key'] }}" data-wysiwyg-target="{{ $v['key'] }}" value="{{ old($v['key'], $d?->{$v['key']} ?? '') }}">
                    <div data-wysiwyg="{{ $v['key'] }}" data-wysiwyg-plain data-placeholder="{{ $v['placeholder'] }}"></div>
                    @error($v['key']) <p class="form-error">{{ $message }}</p> @enderror
                </div>
                @if ($v['correct'])
                    <span class="mt-2 text-xs font-extrabold text-success uppercase tracking-wide shrink-0">✓ верный</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
        <button type="submit" class="btn btn-success">{{ isset($updateRoute) ? 'Сохранить изменения' : 'Добавить вопрос' }}</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
