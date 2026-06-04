@extends('layouts.admin')
@section('title', 'Предметы')
@section('page-title', 'Предметы')
@section('page-subtitle', 'Управление учебными предметами')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div></div>
    <button class="btn btn-primary" disabled>
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Добавить предмет
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Название</th>
                <th>Тем</th>
                <th>Вопросов</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="5" class="text-center text-text-muted py-10">
                    Предметы не добавлены
                </td>
            </tr>
        </tbody>
    </table>
</div>

@endsection
