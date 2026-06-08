@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.groups.show', $group) }}" class="btn btn-outline btn-sm sm:px-5 sm:py-2.5 sm:text-sm">
        <x-icon name="arrow-left" class="w-4 h-4 shrink-0" />
        К группе
    </a>
@endsection

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-5 mb-6">
    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-info-light flex items-center justify-center shrink-0">
            <x-icon name="users" class="w-5 h-5 text-info" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Студентов в группе</p>
            <p class="text-2xl font-extrabold text-text">{{ $students->count() }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-primary-light flex items-center justify-center shrink-0">
            <x-icon name="play" class="w-5 h-5 text-primary" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Проходили тесты</p>
            <p class="text-2xl font-extrabold text-text">{{ $activeStudentsCount }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-success-light flex items-center justify-center shrink-0">
            <x-icon name="chart-bar" class="w-5 h-5 text-success" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Средний балл по группе</p>
            <p class="text-2xl font-extrabold text-text">{{ $averagePercent !== null ? $averagePercent.'%' : '—' }}</p>
        </div>
    </div>
</div>

{{-- Students table --}}
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Студент</th>
                <th class="w-32 text-center">Завершено тестов</th>
                <th class="w-36">Средний балл</th>
                <th class="w-44">Последний тест</th>
                <th class="w-20"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($students as $student)
                @php
                    $completed = $student->tests;
                    $count = $completed->count();
                    $pct = $count > 0
                        ? round($completed->avg(fn ($t) => $t->max_score > 0 ? $t->total_score / $t->max_score * 100 : 0))
                        : null;
                    $isGood = $pct !== null && $pct >= 70;
                    $isMid  = $pct !== null && $pct >= 50 && $pct < 70;
                    $badgeClass = $isGood ? 'badge-success' : ($isMid ? 'badge-primary' : 'badge-danger');
                    $lastTest = $completed->sortByDesc('completed_at')->first();
                @endphp
                <tr class="cursor-pointer" onclick="window.location='{{ route('admin.users.results', $student) }}'">
                    <td>
                        <div class="flex items-center gap-2.5">
                            <x-avatar :name="$student->name" size="sm" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-text truncate">{{ $student->name }}</p>
                                <p class="text-xs text-text-muted font-mono">{{ $student->login }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-center font-mono font-bold text-sm text-text">{{ $count }}</td>
                    <td>
                        @if ($pct !== null)
                            <span class="badge {{ $badgeClass }}">{{ $pct }}%</span>
                        @else
                            <span class="text-text-muted text-xs">ещё не проходил</span>
                        @endif
                    </td>
                    <td class="text-sm text-text-muted">
                        {{ $lastTest?->completed_at?->format('d.m.Y H:i') ?? '—' }}
                    </td>
                    <td class="text-text-muted">
                        <x-icon name="chevron-right" class="w-4 h-4" />
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-text-muted py-12">
                        В группе нет студентов.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection
