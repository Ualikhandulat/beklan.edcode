@php
    $contextOptions = $contexts->mapWithKeys(fn($q) => [
        $q->id => Str::limit(strip_tags($q->text), 80)
    ])->toArray();
    $hasContexts = $contexts->isNotEmpty();
    $defaultMode = $hasContexts ? 'existing' : 'new';
    $isEdit = isset($updateRoute);
    $existingText = $isEdit ? ($q->text ?? '') : '';
@endphp

<form method="POST" action="{{ $updateRoute ?? $storeRoutes['group'] }}"
      x-data="{ mode: '{{ $defaultMode }}' }">
    @csrf
    @isset($updateRoute) @method('PUT') @endisset

    <p class="q-section-label">Контекст (текст для чтения)</p>

    @unless ($isEdit)
        <div class="flex gap-2 mb-4">
            <button type="button" @click="mode = 'new'"
                    :class="mode === 'new' ? 'btn-primary' : 'btn-outline'"
                    class="btn btn-sm">Новый текст</button>
            @if ($hasContexts)
                <button type="button" @click="mode = 'existing'"
                        :class="mode === 'existing' ? 'btn-primary' : 'btn-outline'"
                        class="btn btn-sm">Существующий ({{ $contexts->count() }})</button>
            @endif
        </div>
        <input type="hidden" name="context_mode" x-model="mode">
    @endunless

    <div @if(!$isEdit) x-show="mode === 'new'" @endif class="form-group">
        <input type="hidden" name="context_text" data-wysiwyg-target="context_text" value="{{ old('context_text', $existingText) }}">
        <div data-wysiwyg="context_text" data-wysiwyg-context data-placeholder="Прочитайте текст и ответьте на вопросы..."></div>
        @error('context_text') <p class="form-error">{{ $message }}</p> @enderror
    </div>

    @if ($hasContexts && !$isEdit)
        <div x-show="mode === 'existing'" x-cloak>
            <x-form.select name="context_id" label="Выберите контекст"
                :options="$contextOptions" placeholder="Выберите контекст..." :searchable="true" />
        </div>
    @endif

    @unless ($isEdit)
        <div class="border-t border-border my-6"></div>

        <p class="q-section-label">Подвопрос</p>
        <div class="form-group">
            <input type="hidden" name="question" data-wysiwyg-target="question" value="{{ old('question') }}">
            <div data-wysiwyg="question" data-placeholder="Вопрос к тексту..."></div>
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
                        <input type="hidden" name="{{ $v['key'] }}" data-wysiwyg-target="{{ $v['key'] }}" value="{{ old($v['key']) }}">
                        <div data-wysiwyg="{{ $v['key'] }}" data-wysiwyg-plain data-placeholder="{{ $v['placeholder'] }}"></div>
                        @error($v['key']) <p class="form-error">{{ $message }}</p> @enderror
                    </div>
                    @if ($v['correct'])
                        <span class="mt-2 text-xs font-extrabold text-success uppercase tracking-wide shrink-0">✓ верный</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endunless

    <div class="flex items-center justify-between mt-8 pt-6 border-t border-border">
        <button type="submit" class="btn btn-success">{{ $isEdit ? 'Сохранить изменения' : 'Сохранить подвопрос' }}</button>
        <a href="{{ $showUrl }}" class="btn btn-outline">Отмена</a>
    </div>
</form>
