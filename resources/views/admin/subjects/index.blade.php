@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-success">
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Добавить предмет
    </a>
@endsection

@section('content')

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="w-12 text-text-muted">ID</th>
                <th>Название</th>
                <th>Тем</th>
                <th>Нұсқалар</th>
                <th>Вопросов</th>
                <th>ЕНТ</th>
                <th>Статус</th>
                <th class="w-24"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($subjects as $i => $subject)
                <tr class="cursor-pointer" onclick="window.location='{{ route('admin.subjects.show', $subject) }}'">
                    <td class="text-text-muted text-xs font-mono">{{ $subject->id }}</td>
                    <td>
                        <span class="inline-flex items-center gap-1.5 font-semibold text-info">
                            {{ $subject->title }}
                            <x-icon name="arrow-top-right" class="w-3.5 h-3.5 shrink-0" />
                        </span>
                    </td>
                    <td>{{ $subject->topics_count }}</td>
                    <td>{{ $subject->nusqas_count }}</td>
                    <td>{{ $subject->questions_count }}</td>
                    <td>@if ($subject->is_ent_subject) ✅ @endif</td>
                    <td>
                        @if ($subject->is_active)
                            <span class="badge badge-success">Активен</span>
                        @else
                            <span class="badge badge-danger">Скрыт</span>
                        @endif
                    </td>
                    <td onclick="event.stopPropagation()">
                        @if ($subject->is_mandatory)
                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold text-warning bg-warning/10">
                                <x-icon name="lock-closed" class="w-3 h-3" />
                                Обязательный
                            </span>
                        @else
                            <div class="flex items-center gap-1.5">
                                <x-btn.edit :route="route('admin.subjects.edit', $subject)" />
                                <x-btn.delete :route="route('admin.subjects.destroy', $subject)" />
                            </div>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-text-muted py-12">Предметы не добавлены.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
