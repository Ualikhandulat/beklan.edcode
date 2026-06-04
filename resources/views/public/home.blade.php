@extends('layouts.public')
@section('title', 'Добро пожаловать')

@section('content')

{{-- Hero --}}
<section class="bg-primary">
    <div class="max-w-6xl mx-auto px-6 py-20 text-center">
        <h1 class="text-4xl font-extrabold text-white mb-4 leading-tight">
            Учись. Проверяй. Развивайся.
        </h1>
        <p class="text-lg text-white/80 mb-8 max-w-xl mx-auto">
            EdCode — платформа онлайн-тестирования для школьников. Проходи тесты по предметам и отслеживай свой прогресс.
        </p>
        <a href="{{ route('login') }}" class="btn btn-lg bg-white text-primary hover:bg-gray-50">
            Войти в систему
        </a>
    </div>
</section>

{{-- Features --}}
<section class="max-w-6xl mx-auto px-6 py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="card text-center">
            <div class="w-12 h-12 rounded-2xl bg-primary-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="book-open" class="w-6 h-6 text-primary" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Разнообразные предметы</h3>
            <p class="text-sm text-text-muted">Математика, физика, история и другие дисциплины в одном месте.</p>
        </div>

        <div class="card text-center">
            <div class="w-12 h-12 rounded-2xl bg-info-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="chart-bar" class="w-6 h-6 text-info" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Отслеживание прогресса</h3>
            <p class="text-sm text-text-muted">Мгновенные результаты и история прохождений тестов.</p>
        </div>

        <div class="card text-center">
            <div class="w-12 h-12 rounded-2xl bg-success-light flex items-center justify-center mx-auto mb-4">
                <x-icon name="shield-check" class="w-6 h-6 text-success" />
            </div>
            <h3 class="text-base font-extrabold text-text mb-2">Надёжная система</h3>
            <p class="text-sm text-text-muted">Безопасный доступ, удобное управление пользователями.</p>
        </div>
    </div>
</section>

@endsection
