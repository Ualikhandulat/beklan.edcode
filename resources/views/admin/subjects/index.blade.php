@extends('layouts.admin')
@section('title', 'Предметы')
@section('page-title', 'Предметы')
@section('page-subtitle', 'Управление учебными предметами')

@section('content')

<div class="flex items-center justify-between mb-5">
    <div></div>
    <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary shrink-0">
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Добавить предмет
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="w-12">#</th>
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
                <tr>
                    <td class="text-text-muted">{{ $i + 1 }}</td>
                    <td>
                        <a href="{{ route('admin.subjects.show', $subject) }}" class="font-bold text-text hover:text-primary transition-colors">
                            {{ $subject->title }}
                        </a>
                    </td>
                    <td><span class="badge badge-info">{{ $subject->topics_count }}</span></td>
                    <td><span class="badge badge-primary">{{ $subject->nusqas_count }}</span></td>
                    <td><span class="badge badge-success">{{ $subject->questions_count }}</span></td>
                    <td>
                        @if ($subject->is_ent_subject)
                            <span class="badge badge-primary">ЕНТ</span>
                        @else
                            <span class="text-text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td>
                        @if ($subject->is_active)
                            <span class="badge badge-success">Активен</span>
                        @else
                            <span class="badge badge-danger">Скрыт</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.subjects.edit', $subject) }}"
                               class="btn btn-ghost btn-sm text-text-muted hover:text-info hover:bg-info-light">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                            <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}"
                                  onsubmit="return confirm('Удалить предмет «{{ addslashes($subject->title) }}»?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ghost btn-sm text-text-muted hover:text-danger hover:bg-danger-light">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
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
