@extends('layouts.admin')
@section('title', $topic->exists ? 'Редактировать тему' : 'Добавить тему')
@section('page-title', $topic->exists ? 'Редактировать тему' : 'Добавить тему')
@section('page-subtitle', $subject->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST') @method($method) @endif
            <x-form.input name="title" label="Название темы" :value="$topic->title" :required="true" placeholder="Квадратные уравнения" />
            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="btn btn-primary">
                    {{ $topic->exists ? 'Сохранить' : 'Добавить тему' }}
                </button>
                <a href="{{ route('admin.subjects.show', $subject) }}" class="btn btn-outline">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection
