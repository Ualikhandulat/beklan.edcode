@extends('layouts.admin')

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
                <button type="submit" class="btn btn-success">
                    {{ $user->exists ? 'Сохранить изменения' : 'Создать пользователя' }}
                </button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline">
                    Отмена
                </a>
            </div>
        </form>
    </div>

    {{-- Пробный доступ (только для существующих учеников) --}}
    @if ($user->exists && $user->role === \App\Enums\Role::Student)
        <div class="card mt-5">
            <p class="q-section-label mb-3">Пробный доступ</p>

            @if ($user->has_trial_access)
                <div class="flex items-center gap-2.5">
                    <span class="badge badge-primary inline-flex items-center gap-1.5">
                        <x-icon name="sparkles" class="w-3.5 h-3.5" />
                        Пробный доступ открыт
                    </span>
                </div>
            @elseif ($hasTests ?? false)
                <p class="text-sm text-text-muted">
                    Пробный доступ нельзя открыть: у пользователя уже есть пройденные или начатые тесты.
                </p>
            @else
                <form method="POST" action="{{ route('admin.users.grant-trial', $user) }}"
                      class="flex flex-wrap items-center justify-between gap-3">
                    @csrf
                    @method('PATCH')
                    <p class="text-sm text-text-muted max-w-md">
                        Пользователь увидит активный пробный тест в своём кабинете и сможет пройти его один раз.
                    </p>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <x-icon name="sparkles" class="w-4 h-4" />
                        Открыть пробный доступ
                    </button>
                </form>
            @endif
        </div>
    @endif
</div>

@endsection
