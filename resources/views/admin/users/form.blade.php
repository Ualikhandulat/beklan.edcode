@extends('layouts.admin')
@section('title', $user->exists ? 'Редактировать пользователя' : 'Добавить пользователя')
@section('page-title', $user->exists ? 'Редактировать пользователя' : 'Добавить пользователя')
@section('page-subtitle', $user->exists ? $user->name : 'Заполните данные нового пользователя')

@section('content')

<div class="max-w-2xl mx-auto">
    <div class="card">
        <form method="POST" action="{{ $action }}">
            @csrf
            @if ($method !== 'POST')
                @method($method)
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5">
                <x-form.input
                    name="name"
                    label="Полное имя"
                    placeholder="Иванов Иван"
                    :value="$user->name"
                    :required="true"
                />

                <x-form.input
                    name="login"
                    label="Телефон"
                    type="tel"
                    placeholder="87XXXXXXXXX"
                    :value="$user->login"
                    :required="true"
                />

                <x-form.input
                    name="iin"
                    label="ИИН"
                    placeholder="12 цифр"
                    :value="$user->iin"
                    :required="true"
                />

                <x-form.input
                    name="password"
                    label="Пароль"
                    type="password"
                    :placeholder="$user->exists ? 'Оставьте пустым, чтобы не менять' : 'Минимум 6 символов'"
                    :required="! $user->exists"
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-5">
                <x-form.select
                    name="role"
                    label="Роль"
                    :options="$roles"
                    :selected="$user->role?->value"
                    placeholder="Выберите роль..."
                    :searchable="false"
                    :required="true"
                />

                <x-form.select
                    name="group_id"
                    label="Группа"
                    :options="$groups"
                    :selected="$user->group_id"
                    placeholder="Без группы"
                />
            </div>

            <div class="flex items-center justify-between mt-6">
                <button type="submit" class="btn btn-primary">
                    {{ $user->exists ? 'Сохранить изменения' : 'Создать пользователя' }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</div>

@endsection
