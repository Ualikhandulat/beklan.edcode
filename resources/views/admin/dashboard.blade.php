@extends('layouts.admin')
@section('title', 'Главная')
@section('page-title', 'Главная')
@section('page-subtitle', 'Обзор системы')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-primary-light flex items-center justify-center shrink-0">
            <x-icon name="users" class="w-5 h-5 text-primary" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Пользователи</p>
            <p class="text-2xl font-extrabold text-text">—</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-info-light flex items-center justify-center shrink-0">
            <x-icon name="book-open" class="w-5 h-5 text-info" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Предметы</p>
            <p class="text-2xl font-extrabold text-text">—</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-success-light flex items-center justify-center shrink-0">
            <x-icon name="clipboard-list" class="w-5 h-5 text-success" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Тесты</p>
            <p class="text-2xl font-extrabold text-text">—</p>
        </div>
    </div>
</div>

<div class="card">
    <p class="text-sm text-text-muted text-center py-4">Статистика будет доступна после добавления данных.</p>
</div>

@endsection
