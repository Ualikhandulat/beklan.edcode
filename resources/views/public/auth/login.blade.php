@extends('layouts.public')
@section('title', 'Вход')

@section('content')
<div class="min-h-[calc(100vh-9rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-1 mb-3">
                <span class="text-3xl font-extrabold text-primary">Ed</span><span class="text-3xl font-extrabold text-text">Code</span>
            </div>
            <h2 class="text-xl font-extrabold text-text">Вход в систему</h2>
            <p class="text-sm text-text-muted mt-1">Введите данные для входа</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <x-form.input
                    name="login"
                    label="Номер телефона"
                    type="tel"
                    placeholder="87XXXXXXXXX"
                    :required="true"
                />

                <x-form.input
                    name="password"
                    label="Пароль"
                    type="password"
                    placeholder="••••••••"
                    :required="true"
                />

                <label class="flex items-center gap-2 cursor-pointer mb-5 font-normal">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded accent-primary">
                    <span class="text-sm text-text-muted">Запомнить меня</span>
                </label>

                <button type="submit" class="btn btn-primary w-full btn-lg">
                    Войти
                </button>
            </form>
        </div>

    </div>
</div>
@endsection
