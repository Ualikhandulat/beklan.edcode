<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class LeaderboardService
{
    /**
     * Ranked leaderboard query: students with at least one completed test,
     * ordered by average score percentage (ties broken by attempts count,
     * then name). Each row carries group_title, tests_count, avg_pct, best_pct.
     *
     * @return Builder<User>
     */
    public function query(): Builder
    {
        return User::query()
            ->where('users.role', Role::Student)
            ->join('tests', fn ($join) => $join
                ->on('tests.user_id', '=', 'users.id')
                ->whereNotNull('tests.completed_at')
                ->where('tests.max_score', '>', 0))
            ->leftJoin('groups', 'groups.id', '=', 'users.group_id')
            ->groupBy('users.id', 'users.name', 'users.login', 'groups.title')
            ->selectRaw('
                users.id,
                users.name,
                users.login,
                groups.title as group_title,
                COUNT(tests.id) as tests_count,
                ROUND(AVG(tests.total_score / tests.max_score * 100)) as avg_pct,
                ROUND(MAX(tests.total_score / tests.max_score * 100)) as best_pct
            ')
            ->orderByDesc('avg_pct')
            ->orderByDesc('tests_count')
            ->orderBy('users.name');
    }

    /**
     * Full ranked list as plain cache-serializable rows.
     *
     * @return list<array{id: int, name: string, group: ?string, tests: int, avgPct: int, bestPct: int}>
     */
    public function rows(): array
    {
        return $this->query()
            ->get()
            ->map(fn ($row) => [
                'id' => (int) $row->id,
                'name' => $row->name,
                'group' => $row->group_title,
                'tests' => (int) $row->tests_count,
                'avgPct' => (int) $row->avg_pct,
                'bestPct' => (int) $row->best_pct,
            ])
            ->all();
    }
}
