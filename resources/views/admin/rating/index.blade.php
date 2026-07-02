@extends('layouts.admin')

@section('content')

{{-- Rating table --}}
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th class="w-20">Место</th>
                <th>Ученик</th>
                <th class="w-40">Группа</th>
                <th class="w-24">Тестов</th>
                <th class="w-36">Средний балл</th>
                <th class="w-36">Лучший результат</th>
                <th class="w-16"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                @php
                    $rank = $rows->firstItem() + $loop->index;
                    $medal = match ($rank) {
                        1 => 'background: linear-gradient(135deg, #FFD76A, #F2A93B); color: #fff; box-shadow: 0 2px 8px rgba(242,169,59,0.45)',
                        2 => 'background: linear-gradient(135deg, #D7DDE4, #A9B2BC); color: #fff; box-shadow: 0 2px 8px rgba(169,178,188,0.45)',
                        3 => 'background: linear-gradient(135deg, #E8B189, #C98A57); color: #fff; box-shadow: 0 2px 8px rgba(201,138,87,0.45)',
                        default => null,
                    };
                    $avgPct  = (int) $row->avg_pct;
                    $bestPct = (int) $row->best_pct;
                    $avgBadge  = $avgPct >= 70 ? 'badge-success' : ($avgPct >= 50 ? 'badge-primary' : 'badge-danger');
                    $bestBadge = $bestPct >= 70 ? 'badge-success' : ($bestPct >= 50 ? 'badge-primary' : 'badge-danger');
                @endphp
                <tr>
                    <td>
                        <span class="w-8 h-8 rounded-full inline-flex items-center justify-center text-sm font-black tabular-nums {{ $medal ? '' : 'bg-gray-100 text-text-muted' }}"
                              @if ($medal) style="{{ $medal }}" @endif>
                            {{ $rank }}
                        </span>
                    </td>
                    <td>
                        <div class="flex items-center gap-2.5">
                            <x-avatar :name="$row->name" size="sm" />
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-text truncate">{{ $row->name }}</p>
                                <p class="text-xs text-text-muted font-mono">{{ $row->login }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="text-sm text-text-muted">{{ $row->group_title ?? '—' }}</td>
                    <td class="text-sm font-semibold text-text tabular-nums">{{ $row->tests_count }}</td>
                    <td><span class="badge {{ $avgBadge }}">{{ $avgPct }}%</span></td>
                    <td><span class="badge {{ $bestBadge }}">{{ $bestPct }}%</span></td>
                    <td>
                        <a href="{{ route('admin.users.results', $row->id) }}"
                           class="btn btn-ghost btn-sm text-text-muted hover:text-primary hover:bg-primary-light"
                           title="Результаты ученика">
                            <x-icon name="eye" class="w-4 h-4" />
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center text-text-muted py-12">
                        Пока нет завершённых тестов — рейтинг пуст.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($rows->hasPages())
        <div class="px-5 py-4 border-t border-border">
            {{ $rows->links() }}
        </div>
    @endif
</div>

@endsection
