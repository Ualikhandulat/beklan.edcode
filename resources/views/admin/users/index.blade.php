@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.users.create') }}" class="btn btn-success btn-sm">
        <x-icon name="plus" class="w-4 h-4 shrink-0" />
        Добавить пользователя
    </a>
@endsection

@section('content')

<div class="flex items-center gap-4 mb-5">
    <form method="GET" action="{{ route('admin.users.index') }}" class="flex-1 max-w-sm">
        <div class="relative flex items-center gap-2">
            <div class="relative flex-1">
                <x-icon name="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
                <input
                    type="search"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Поиск по имени, телефону, ИИН..."
                    class="pl-10"
                >
            </div>
            @if (request('search'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost btn-sm shrink-0 text-text-muted hover:text-danger hover:bg-danger-light">
                    <x-icon name="x" class="w-4 h-4" />
                </a>
            @endif
        </div>
    </form>

</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="w-12">#</th>
                <th>Имя</th>
                <th>Телефон</th>
                <th>ИИН</th>
                <th>Роль</th>
                <th>Группа</th>
                <th class="w-24"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
                <tr>
                    <td class="text-text-muted">{{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}</td>
                    <td class="font-semibold">{{ $user->name }}</td>
                    <td class="font-mono">{{ $user->login }}</td>
                    <td class="font-mono text-text-muted">{{ $user->iin }}</td>
                    <td>
                        @if ($user->role === \App\Enums\Role::Admin)
                            <span class="badge badge-primary">{{ $user->role->title() }}</span>
                        @else
                            <span class="badge badge-info">{{ $user->role->title() }}</span>
                        @endif
                    </td>
                    <td class="text-text-muted">{{ $user->group?->title ?? '—' }}</td>
                    <td>
                        <div class="flex items-center gap-1.5">
                            <x-btn.edit :route="route('admin.users.edit', $user)" />
                            <x-btn.delete :route="route('admin.users.destroy', $user)" />
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-text-muted py-12">
                        @if (request('search'))
                            По запросу «{{ request('search') }}» ничего не найдено.
                        @else
                            Пользователи не добавлены.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($users->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
