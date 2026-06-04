@extends('layouts.admin')

@section('content')

<div class="flex items-center justify-between gap-4 mb-5">
    <form method="GET" action="{{ route('admin.groups.index') }}" class="flex-1 max-w-sm">
        <div class="relative flex items-center gap-2">
            <div class="relative flex-1">
                <x-icon name="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Поиск по названию..."
                    class="pl-10"
                >
            </div>
            @if (request('search'))
                <a href="{{ route('admin.groups.index') }}" class="btn btn-ghost btn-sm shrink-0 text-text-muted hover:text-danger hover:bg-danger-light">
                    <x-icon name="x" class="w-4 h-4" />
                </a>
            @endif
        </div>
    </form>

    <a href="{{ route('admin.groups.create') }}" class="btn btn-success shrink-0">
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Добавить группу
    </a>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="w-12">#</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Студентов</th>
                <th class="w-24"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $group)
                <tr>
                    <td class="text-text-muted">{{ $loop->iteration + ($groups->currentPage() - 1) * $groups->perPage() }}</td>
                    <td class="font-semibold">{{ $group->title }}</td>
                    <td class="text-text-muted max-w-xs truncate">{{ $group->description }}</td>
                    <td>
                        <span class="badge badge-info">{{ $group->users_count }}</span>
                    </td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <a href="{{ route('admin.groups.edit', $group) }}"
                               class="btn btn-ghost btn-sm text-text-muted hover:text-info hover:bg-info-light">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </a>
                            <form method="POST" action="{{ route('admin.groups.destroy', $group) }}"
                                  onsubmit="return confirm('Удалить группу «{{ addslashes($group->title) }}»?')">
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
                    <td colspan="5" class="text-center text-text-muted py-12">
                        @if (request('search'))
                            По запросу «{{ request('search') }}» ничего не найдено.
                        @else
                            Группы не добавлены.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($groups->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $groups->links() }}
        </div>
    @endif
</div>

@endsection
