@extends('layouts.public')
@section('title', __('Регистрация'))

@section('content')
<div class="min-h-[calc(100vh-9rem)] flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-sm">

        <div class="text-center mb-8">
            <div class="inline-flex items-center gap-1 mb-3">
                <span class="text-3xl font-extrabold text-primary">Ed</span><span class="text-3xl font-extrabold text-text">Code</span>
            </div>
            <h2 class="text-xl font-extrabold text-text">{{ __('Регистрация') }}</h2>
            <p class="text-sm text-text-muted mt-1">{{ __('Создайте аккаунт и пройдите пробный тест') }}</p>
        </div>

        <div class="card">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <x-form.input
                    name="name"
                    label="{{ __('Полное имя') }}"
                    placeholder="{{ __('Фамилия Имя') }}"
                    :value="old('name')"
                    :required="true"
                />

                <x-form.input
                    name="login"
                    label="{{ __('Номер телефона') }}"
                    type="tel"
                    placeholder="87XXXXXXXXX"
                    :value="old('login')"
                    :required="true"
                />

                <x-form.input
                    name="iin"
                    label="{{ __('ИИН') }}"
                    placeholder="{{ __('12 цифр') }}"
                    :value="old('iin')"
                    :required="true"
                />

                <x-form.input
                    name="password"
                    label="{{ __('Пароль') }}"
                    type="password"
                    placeholder="{{ __('Минимум 6 символов') }}"
                    :required="true"
                />

                <x-form.input
                    name="password_confirmation"
                    label="{{ __('Повторите пароль') }}"
                    type="password"
                    placeholder="••••••••"
                    :required="true"
                />

                <button type="submit" class="btn btn-primary w-full btn-lg">
                    {{ __('Зарегистрироваться') }}
                </button>
            </form>
        </div>

        <p class="text-center text-sm text-text-muted mt-5">
            {{ __('Уже есть аккаунт?') }}
            <a href="{{ route('login') }}" class="font-semibold text-primary hover:underline">{{ __('Войти') }}</a>
        </p>

    </div>
</div>
@endsection
