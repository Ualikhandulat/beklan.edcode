@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.results.export', request()->query()) }}" class="btn btn-success btn-sm sm:px-5 sm:py-2.5 sm:text-sm">
        <x-icon name="download" class="w-4 h-4 shrink-0" />
        Экспорт в Excel
    </a>
@endsection

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-info-light flex items-center justify-center shrink-0">
            <x-icon name="clipboard-list" class="w-5 h-5 text-info" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Всего попыток</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['total'] }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-primary-light flex items-center justify-center shrink-0">
            <x-icon name="users" class="w-5 h-5 text-primary" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Студентов</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['students'] }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-success-light flex items-center justify-center shrink-0">
            <x-icon name="check" class="w-5 h-5 text-success" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Завершено</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['completed'] }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-danger-light flex items-center justify-center shrink-0">
            <x-icon name="chart-bar" class="w-5 h-5 text-danger" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Средний балл</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['averagePercent'] !== null ? $stats['averagePercent'].'%' : '—' }}</p>
        </div>
    </div>
</div>

{{-- Filters --}}
<form method="GET" action="{{ route('admin.results.index') }}" class="flex flex-wrap items-center gap-2 sm:gap-3 mb-5">

    <div class="relative w-full sm:w-64 sm:shrink-0">
        <x-icon name="search" class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-text-muted pointer-events-none" />
        <input type="search" name="search" value="{{ request('search') }}"
               placeholder="Имя, логин или ИИН..."
               class="pl-10">
    </div>

    <div class="flex-1 sm:flex-none sm:w-44">
        <x-form.select name="group_id" style="margin-bottom: 0"
                       :options="['' => 'Все группы'] + $groups->all()"
                       :selected="request('group_id')"
                       placeholder="Все группы"
                       onchange="this.form.submit()" />
    </div>

    <div class="flex-1 sm:flex-none sm:w-44">
        <x-form.select name="subject_id" style="margin-bottom: 0"
                       :options="['' => 'Все предметы'] + $subjects->all()"
                       :selected="request('subject_id')"
                       placeholder="Все предметы"
                       onchange="this.form.submit()" />
    </div>

    <div class="flex-1 sm:flex-none sm:w-36">
        <x-form.select name="type" style="margin-bottom: 0"
                       :options="['' => 'Все типы'] + $types->all()"
                       :selected="request('type')"
                       placeholder="Все типы"
                       :searchable="false"
                       onchange="this.form.submit()" />
    </div>

    <div class="flex-1 sm:flex-none sm:w-44">
        <x-form.select name="status" style="margin-bottom: 0"
                       :options="['' => 'Все статусы'] + $statuses->all()"
                       :selected="request('status')"
                       placeholder="Все статусы"
                       :searchable="false"
                       onchange="this.form.submit()" />
    </div>

    <div class="flex items-center gap-2">
        <input type="date" name="date_from" value="{{ request('date_from') }}"
               onchange="this.form.submit()" class="w-40" title="С даты">
        <span class="text-text-muted text-sm">—</span>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
               onchange="this.form.submit()" class="w-40" title="По дату">
    </div>

    @if (request()->hasAny(['search', 'group_id', 'subject_id', 'type', 'status', 'date_from', 'date_to']))
        <a href="{{ route('admin.results.index') }}"
           class="btn btn-ghost btn-sm shrink-0 text-text-muted hover:text-danger hover:bg-danger-light">
            <x-icon name="x" class="w-4 h-4" />
        </a>
    @endif

</form>

{{-- Results table --}}
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Студент</th>
                <th class="w-32">Группа</th>
                <th class="w-24">Тип</th>
                <th>Предметы</th>
                <th class="w-32">Балл</th>
                <th class="w-28">Длительность</th>
                <th class="w-40">Завершён</th>
                <th class="w-28">Статус</th>
                <th class="w-16"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tests as $test)
                @php
                    $pct = $test->percent();
                    $isGood = $pct !== null && $pct >= 70;
                    $isMid  = $pct !== null && $pct >= 50 && $pct < 70;
                    $badgeClass = $isGood ? 'badge-success' : ($isMid ? 'badge-primary' : 'badge-danger');
                @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-2.5">
                            <x-avatar :name="$test->user?->name ?? '—'" size="sm" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-text truncate">{{ $test->user?->name ?? '—' }}</p>
                                <p class="text-xs text-text-muted font-mono">{{ $test->user?->login }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-text-muted">{{ $test->user?->group?->title ?? '—' }}</td>
                    <td>
                        @if ($test->access)
                            <span class="badge {{ $test->access->type->badgeClass() }}">{{ $test->access->type->label() }}</span>
                        @else
                            <span class="text-text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="space-y-0.5">
                            @forelse ($test->subjects as $ts)
                                <p class="text-xs text-text">
                                    {{ $ts->subject?->title ?? '—' }}
                                    @if ($test->isCompleted())
                                        <span class="font-mono font-semibold text-text-muted">{{ $ts->score }}/{{ $ts->max_score }}</span>
                                    @endif
                                </p>
                            @empty
                                <span class="text-text-muted text-xs">—</span>
                            @endforelse
                        </div>
                    </td>
                    <td>
                        @if ($pct !== null)
                            <span class="badge {{ $badgeClass }}">
                                {{ $test->total_score }}/{{ $test->max_score }} ({{ $pct }}%)
                            </span>
                        @else
                            <span class="text-text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="text-sm text-text-muted">{{ $test->durationLabel() ?? '—' }}</td>
                    <td class="text-sm">{{ $test->completed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                    <td>
                        @if ($test->isCompleted())
                            <span class="badge badge-success">Завершён</span>
                        @elseif ($test->isExpired())
                            <span class="badge badge-danger">Истёк</span>
                        @else
                            <span class="badge badge-primary">В процессе</span>
                        @endif
                    </td>
                    <td>
                        @if ($test->isCompleted())
                            <a href="{{ route('admin.results.show', $test) }}"
                               class="btn btn-ghost btn-sm text-text-muted hover:text-primary hover:bg-primary-light"
                               title="Разбор ответов">
                                <x-icon name="eye" class="w-4 h-4" />
                            </a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center text-text-muted py-12">
                        @if (request()->hasAny(['search', 'group_id', 'subject_id', 'type', 'status', 'date_from', 'date_to']))
                            По фильтрам ничего не найдено.
                        @else
                            Результатов пока нет.
                        @endif
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($tests->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $tests->links() }}
        </div>
    @endif
</div>

@endsection
