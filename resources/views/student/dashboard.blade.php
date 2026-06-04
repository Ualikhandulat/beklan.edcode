@extends('layouts.student')
@section('title', 'Личный кабинет')

@section('content')

<div class="text-center py-16">
    <div class="w-16 h-16 rounded-full bg-primary-light flex items-center justify-center mx-auto mb-5">
        <span class="text-2xl font-extrabold text-primary">
            {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
        </span>
    </div>
    <h1 class="text-2xl font-extrabold text-text mb-2">
        Добро пожаловать, {{ auth()->user()->name }}!
    </h1>
    <p class="text-text-muted text-sm max-w-sm mx-auto">
        Здесь скоро появятся ваши тесты и результаты. Следите за обновлениями.
    </p>
</div>

@endsection
