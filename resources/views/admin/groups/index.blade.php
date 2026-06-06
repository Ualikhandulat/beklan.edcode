@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.groups.create') }}" class="btn btn-success btn-sm sm:px-5 sm:py-2.5 sm:text-sm">
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Добавить группу
    </a>
@endsection

@section('content')

<div class="flex items-center gap-4 mb-5">
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

</div>

<div class="table-container">
    <table class="whitespace-nowrap">
        <thead>
            <tr>
                <th class="w-12 text-text-muted hidden sm:table-cell">ID</th>
                <th>Название</th>
                <th>Описание</th>
                <th>Студентов</th>
                <th class="w-24"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $group)
                <tr class="cursor-pointer" onclick="window.location='{{ route('admin.groups.show', $group) }}'">
                    <td class="text-text-muted text-xs font-mono hidden sm:table-cell">{{ $group->id }}</td>
                    <td>
                        <div class="flex items-center gap-3">
                            <x-avatar :name="$group->title" size="sm" :singleLetter="true" />
                            <span class="font-bold text-text">{{ $group->title }}</span>
                        </div>
                    </td>
                    <td class="text-text-muted">{{ $group->description }}</td>
                    <td>
                        <span class="badge badge-info">{{ $group->users_count }}</span>
                    </td>
                    <td onclick="event.stopPropagation()">
                        <div class="flex items-center gap-1.5">
                            <x-btn.edit :route="route('admin.groups.edit', $group)" />
                            <x-btn.delete :route="route('admin.groups.destroy', $group)" />
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
