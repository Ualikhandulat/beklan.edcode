@extends('layouts.admin')

@section('actions')
    <a href="{{ route('admin.test-accesses.index') }}" class="btn btn-outline btn-sm sm:px-5 sm:py-2.5 sm:text-sm">
        <x-icon name="arrow-left" class="w-4 h-4 shrink-0" />
        К доступам
    </a>
@endsection

@section('content')

{{-- Summary cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-info-light flex items-center justify-center shrink-0">
            <x-icon name="users" class="w-5 h-5 text-info" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Назначено</p>
            <p class="text-2xl font-extrabold text-text">{{ $assignedCount }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-primary-light flex items-center justify-center shrink-0">
            <x-icon name="play" class="w-5 h-5 text-primary" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Начали</p>
            <p class="text-2xl font-extrabold text-text">{{ $startedCount }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-success-light flex items-center justify-center shrink-0">
            <x-icon name="check" class="w-5 h-5 text-success" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Завершили</p>
            <p class="text-2xl font-extrabold text-text">{{ $completedCount }}</p>
        </div>
    </div>

    <div class="card card-sm flex items-center gap-4">
        <div class="w-11 h-11 rounded-2xl bg-danger-light flex items-center justify-center shrink-0">
            <x-icon name="chart-bar" class="w-5 h-5 text-danger" />
        </div>
        <div>
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Средний балл</p>
            <p class="text-2xl font-extrabold text-text">{{ $averagePercent !== null ? $averagePercent.'%' : '—' }}</p>
        </div>
    </div>
</div>

{{-- Attempts table --}}
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Студент</th>
                <th class="w-20 text-center">Попытка</th>
                <th class="w-32">Балл</th>
                <th class="w-28">Длительность</th>
                <th class="w-40">Завершён</th>
                <th class="w-32">Статус</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($tests as $test)
                @php
                    $pct = ($test->isCompleted() && $test->max_score > 0)
                        ? round($test->total_score / $test->max_score * 100)
                        : null;
                    $isGood = $pct !== null && $pct >= 70;
                    $isMid  = $pct !== null && $pct >= 50 && $pct < 70;
                    $badgeClass = $isGood ? 'badge-success' : ($isMid ? 'badge-primary' : 'badge-danger');

                    $duration = ($test->started_at && $test->completed_at)
                        ? $test->started_at->diffInSeconds($test->completed_at)
                        : null;
                    $durationStr = null;
                    if ($duration !== null) {
                        $h = intdiv($duration, 3600);
                        $m = intdiv($duration % 3600, 60);
                        $s = $duration % 60;
                        $durationStr = $h > 0 ? "{$h}ч {$m}м" : ($m > 0 ? "{$m}м {$s}с" : "{$s}с");
                    }
                @endphp
                <tr>
                    <td>
                        <div class="flex items-center gap-2.5">
                            <x-avatar :name="$test->user->name" size="sm" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-text truncate">{{ $test->user->name }}</p>
                                <p class="text-xs text-text-muted font-mono">{{ $test->user->login }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-center font-mono text-sm text-text-muted">#{{ $test->attempt_number }}</td>
                    <td>
                        @if ($pct !== null)
                            <span class="badge {{ $badgeClass }}">
                                {{ $test->total_score }}/{{ $test->max_score }} ({{ $pct }}%)
                            </span>
                        @else
                            <span class="text-text-muted text-xs">—</span>
                        @endif
                    </td>
                    <td class="text-sm text-text-muted">{{ $durationStr ?? '—' }}</td>
                    <td class="text-sm">
                        {{ $test->completed_at?->format('d.m.Y H:i') ?? '—' }}
                    </td>
                    <td>
                        @if ($test->isCompleted())
                            <span class="badge badge-success">Завершён</span>
                        @elseif ($test->isExpired())
                            <span class="badge badge-danger">Истёк</span>
                        @else
                            <span class="badge badge-primary">В процессе</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center text-text-muted py-12">
                        Никто ещё не приступил к этому тесту.
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
