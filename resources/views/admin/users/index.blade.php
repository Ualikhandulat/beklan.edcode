@extends('layouts.admin')
@section('title', 'Пользователи')
@section('page-title', 'Пользователи')
@section('page-subtitle', 'Управление пользователями системы')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div></div>
    <button class="btn btn-primary" disabled>
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Добавить пользователя
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>ИИН</th>
                <th>Роль</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" class="text-center text-text-muted py-10">
                    Пользователи не добавлены
                </td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
