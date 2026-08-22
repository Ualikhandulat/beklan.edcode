@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            <input type="hidden" name="type" value="{{ $part->type?->value ?? request('type', 'topic') }}">

            @if ($part->type === \App\Enums\PartType::Nusqa)
                <x-form.input name="title" label="Нұсқа (номер)" :value="$part->title" :required="true"
                    type="number" placeholder="1" />

                <div class="p-4 rounded-2xl border border-primary/25 bg-primary/5 mb-5">
                    <x-form.toggle name="is_trial" label="Пробный нұсқа" :checked="$isTrialPart ?? false" />
                    <p class="text-xs text-text-muted mt-2 leading-relaxed">
                        Этот нұсқа станет пробным тестом (1 попытка, все вопросы нұсқа): его автоматически
                        получают все, кто зарегистрировался самостоятельно, и ученики, которым вы открыли
                        пробный доступ. Для остальных студентов он будет скрыт из списков выбора.
                        Пробным может быть только один нұсқа.
                    </p>
                </div>
            @else
                <x-form.input name="title" label="Название" :value="$part->title" :required="true"
                    placeholder="{{ $part->type?->label() ?? 'Название' }}" />
            @endif

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="btn btn-success">
                    {{ $part->exists ? 'Сохранить' : ($part->type?->createLabel() ?? 'Добавить') }}
                </button>
                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
