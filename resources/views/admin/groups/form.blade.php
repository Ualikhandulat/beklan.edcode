@extends('layouts.admin')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <x-form.input
                name="title"
                label="Название группы"
                placeholder="Например: 11А, Группа 2025"
                :value="$group->title"
                :required="true"
            />

            <x-form.textarea
                name="description"
                label="Описание"
                placeholder="Краткое описание группы..."
                :value="$group->description"
                :rows="4"
                :required="true"
            />

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="btn btn-success">
                    {{ $group->exists ? 'Сохранить изменения' : 'Создать группу' }}
                </button>
                <a href="{{ route('admin.groups.index') }}" class="btn btn-outline">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
