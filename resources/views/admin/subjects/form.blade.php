@extends('layouts.admin')
@section('title', $subject->exists ? 'Редактировать предмет' : 'Добавить предмет')
@section('page-title', $subject->exists ? 'Редактировать предмет' : 'Добавить предмет')
@section('page-subtitle', $subject->exists ? $subject->title : 'Заполните данные нового предмета')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST') @method($method) @endif

            <x-form.input
                name="title"
                label="Название предмета"
                placeholder="Математика"
                :value="$subject->title"
                :required="true"
            />

            <div class="grid grid-cols-2 gap-5">
                <x-form.toggle
                    name="is_ent_subject"
                    label="Предмет ЕНТ"
                    :checked="$subject->is_ent_subject"
                />
                <x-form.toggle
                    name="is_active"
                    label="Активен"
                    :checked="$subject->exists ? $subject->is_active : true"
                />
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="btn btn-primary">
                    {{ $subject->exists ? 'Сохранить изменения' : 'Создать предмет' }}
                </button>
                <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>

@endsection
