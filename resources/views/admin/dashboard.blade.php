@extends('layouts.admin')

@section('content')

@php
    $distTotal = array_sum($stats['distribution']);
@endphp

{{-- ── Stat cards (clickable → sections) ──────────────────────────────── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-6">
    <a href="{{ route('admin.users.index') }}" class="card card-sm flex items-center gap-4 transition-all duration-200 hover:-translate-y-0.5" style="box-shadow: var(--shadow-card)">
        <div class="w-11 h-11 rounded-2xl bg-info-light flex items-center justify-center shrink-0">
            <x-icon name="users" class="w-5 h-5 text-info" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Студенты</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['counts']['students'] }}</p>
        </div>
        <x-icon name="arrow-right" class="w-4 h-4 text-text-muted ml-auto shrink-0" />
    </a>

    <a href="{{ route('admin.groups.index') }}" class="card card-sm flex items-center gap-4 transition-all duration-200 hover:-translate-y-0.5" style="box-shadow: var(--shadow-card)">
        <div class="w-11 h-11 rounded-2xl bg-primary-light flex items-center justify-center shrink-0">
            <x-icon name="user-group" class="w-5 h-5 text-primary" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Группы</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['counts']['groups'] }}</p>
        </div>
        <x-icon name="arrow-right" class="w-4 h-4 text-text-muted ml-auto shrink-0" />
    </a>

    <a href="{{ route('admin.subjects.index') }}" class="card card-sm flex items-center gap-4 transition-all duration-200 hover:-translate-y-0.5" style="box-shadow: var(--shadow-card)">
        <div class="w-11 h-11 rounded-2xl bg-success-light flex items-center justify-center shrink-0">
            <x-icon name="book-open" class="w-5 h-5 text-success" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Предметы</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['counts']['subjects'] }}</p>
        </div>
        <x-icon name="arrow-right" class="w-4 h-4 text-text-muted ml-auto shrink-0" />
    </a>

    <a href="{{ route('admin.test-accesses.index') }}" class="card card-sm flex items-center gap-4 transition-all duration-200 hover:-translate-y-0.5" style="box-shadow: var(--shadow-card)">
        <div class="w-11 h-11 rounded-2xl bg-danger-light flex items-center justify-center shrink-0">
            <x-icon name="clipboard-list" class="w-5 h-5 text-danger" />
        </div>
        <div class="min-w-0">
            <p class="text-xs font-bold text-text-muted uppercase tracking-wide">Доступы / тесты</p>
            <p class="text-2xl font-extrabold text-text">{{ $stats['counts']['accesses'] }}</p>
        </div>
        <x-icon name="arrow-right" class="w-4 h-4 text-text-muted ml-auto shrink-0" />
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Activity over last 14 days --}}
    <div class="lg:col-span-2 card">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="font-extrabold text-text">Активность прохождения тестов</p>
                <p class="text-xs text-text-muted mt-0.5">Завершённых тестов за последние 14 дней</p>
            </div>
            <span class="text-xs font-bold text-text-muted bg-gray-100 px-2.5 py-1 rounded-full">
                {{ $stats['activity']->sum('count') }} всего
            </span>
        </div>
        <div class="h-64">
            <canvas id="activityChart"></canvas>
        </div>
    </div>

    {{-- Score distribution donut --}}
    <div class="card flex flex-col">
        <p class="font-extrabold text-text mb-1">Распределение баллов</p>
        <p class="text-xs text-text-muted mb-4">По всем завершённым тестам</p>

        @if ($distTotal > 0)
            <div class="relative flex-1 flex items-center justify-center min-h-[180px]">
                <canvas id="distributionChart" class="max-h-[200px]"></canvas>
                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                    <span class="text-2xl font-extrabold text-text">{{ $stats['avgPct'] }}%</span>
                    <span class="text-[11px] text-text-muted font-semibold">средний балл</span>
                </div>
            </div>
            <div class="flex items-center justify-center gap-4 mt-4 text-xs">
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background: var(--color-success)"></span> ≥70% <b class="text-text">{{ $stats['distribution']['good'] }}</b></span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background: var(--color-primary)"></span> 50–69% <b class="text-text">{{ $stats['distribution']['mid'] }}</b></span>
                <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full" style="background: var(--color-danger)"></span> &lt;50% <b class="text-text">{{ $stats['distribution']['low'] }}</b></span>
            </div>
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-center py-8">
                <div class="w-12 h-12 rounded-2xl bg-gray-100 flex items-center justify-center mb-3">
                    <x-icon name="chart-bar" class="w-6 h-6 text-text-muted" />
                </div>
                <p class="text-sm font-bold text-text">Пока нет данных</p>
                <p class="text-xs text-text-muted mt-1">Появятся после первых завершённых тестов</p>
            </div>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

    {{-- Top subjects --}}
    <div class="card">
        <p class="font-extrabold text-text mb-1">Предметы по активности</p>
        <p class="text-xs text-text-muted mb-4">Средний балл и число попыток</p>

        @if ($stats['subjects']->isNotEmpty())
            <div class="h-64">
                <canvas id="subjectsChart"></canvas>
            </div>
        @else
            <p class="text-sm text-text-muted text-center py-12">Пока нет данных по предметам.</p>
        @endif
    </div>

    {{-- Top groups leaderboard --}}
    <div class="card">
        <p class="font-extrabold text-text mb-1">Лучшие группы</p>
        <p class="text-xs text-text-muted mb-4">По среднему баллу завершённых тестов</p>

        @if ($stats['groups']->isNotEmpty())
            <div class="space-y-1">
                @foreach ($stats['groups'] as $i => $group)
                    @php
                        $isGood = $group['pct'] >= 70;
                        $isMid  = $group['pct'] >= 50 && $group['pct'] < 70;
                        $badgeClass = $isGood ? 'badge-success' : ($isMid ? 'badge-primary' : 'badge-danger');
                    @endphp
                    <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                        <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-extrabold shrink-0
                                     {{ $i === 0 ? 'bg-primary text-white' : 'bg-gray-100 text-text-muted' }}">
                            {{ $i + 1 }}
                        </span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-text truncate">{{ $group['title'] }}</p>
                            <p class="text-xs text-text-muted">{{ $group['attempts'] }} завершённых тестов</p>
                        </div>
                        <span class="badge {{ $badgeClass }} shrink-0">{{ $group['pct'] }}%</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-sm text-text-muted text-center py-12">Пока нет завершённых тестов в группах.</p>
        @endif
    </div>
</div>

{{-- Recent activity --}}
<div class="card">
    <div class="flex items-center justify-between mb-4">
        <p class="font-extrabold text-text">Последние завершённые тесты</p>
        <a href="{{ route('admin.test-accesses.index') }}" class="text-xs font-bold text-primary hover:underline flex items-center gap-1">
            Все доступы
            <x-icon name="arrow-right" class="w-3.5 h-3.5" />
        </a>
    </div>

    @if ($stats['recent']->isNotEmpty())
        <div class="divide-y divide-border">
            @foreach ($stats['recent'] as $test)
                @php
                    $pct = $test->max_score > 0 ? round($test->total_score / $test->max_score * 100) : 0;
                    $isGood = $pct >= 70;
                    $isMid  = $pct >= 50 && $pct < 70;
                    $badgeClass = $isGood ? 'badge-success' : ($isMid ? 'badge-primary' : 'badge-danger');
                @endphp
                <a href="{{ route('admin.users.results', $test->user) }}"
                   class="flex items-center gap-3 py-3 hover:bg-gray-50 -mx-2 px-2 rounded-xl transition-colors">
                    <x-avatar :name="$test->user->name" size="sm" />
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-text truncate">{{ $test->user->name }}</p>
                        <p class="text-xs text-text-muted">{{ $test->completed_at->format('d.m.Y H:i') }}</p>
                    </div>
                    <span class="badge {{ $badgeClass }} shrink-0">{{ $test->total_score }}/{{ $test->max_score }} ({{ $pct }}%)</span>
                </a>
            @endforeach
        </div>
    @else
        <p class="text-sm text-text-muted text-center py-12">Пока никто не завершил ни одного теста.</p>
    @endif
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const muted = '#9CA3AF';
        const grid  = '#F3F4F6';
        Chart.defaults.font.family = 'Nunito, sans-serif';
        Chart.defaults.color = muted;

        // ── Activity (last 14 days) ──────────────────────────────────────
        const activityCtx = document.getElementById('activityChart');
        if (activityCtx) {
            new Chart(activityCtx, {
                type: 'bar',
                data: {
                    labels: {!! Js::from($stats['activity']->pluck('label')) !!},
                    datasets: [{
                        label: 'Завершено тестов',
                        data: {!! Js::from($stats['activity']->pluck('count')) !!},
                        backgroundColor: '#F2994A',
                        borderRadius: 6,
                        maxBarThickness: 28,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: grid } },
                    },
                },
            });
        }

        // ── Score distribution donut ─────────────────────────────────────
        const distCtx = document.getElementById('distributionChart');
        if (distCtx) {
            new Chart(distCtx, {
                type: 'doughnut',
                data: {
                    labels: ['≥70%', '50–69%', '<50%'],
                    datasets: [{
                        data: [
                            {{ $stats['distribution']['good'] }},
                            {{ $stats['distribution']['mid'] }},
                            {{ $stats['distribution']['low'] }},
                        ],
                        backgroundColor: ['#27AE60', '#F2994A', '#EB5757'],
                        borderWidth: 0,
                        spacing: 2,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: { legend: { display: false } },
                },
            });
        }

        // ── Subjects ──────────────────────────────────────────────────────
        const subjectsCtx = document.getElementById('subjectsChart');
        if (subjectsCtx) {
            new Chart(subjectsCtx, {
                type: 'bar',
                data: {
                    labels: {!! Js::from($stats['subjects']->pluck('title')) !!},
                    datasets: [{
                        label: 'Средний балл, %',
                        data: {!! Js::from($stats['subjects']->pluck('pct')) !!},
                        backgroundColor: '#2D9CDB',
                        borderRadius: 6,
                        maxBarThickness: 22,
                    }],
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, max: 100, grid: { color: grid } },
                        y: { grid: { display: false } },
                    },
                },
            });
        }
    });
</script>
@endpush
