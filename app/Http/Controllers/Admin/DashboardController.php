<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Subject;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\TestSubject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = $this->loadStats();

        return view('admin.dashboard', compact('stats'));
    }

    /**
     * Build platform-wide statistics for the dashboard using a small,
     * fixed number of aggregate queries (no N+1).
     *
     * @return array{
     *     counts: array{students: int, groups: int, subjects: int, accesses: int},
     *     totalCompleted: int,
     *     avgPct: int,
     *     distribution: array{good: int, mid: int, low: int},
     *     activity: Collection<int, array{label: string, date: string, count: int}>,
     *     subjects: Collection<int, array{title: string, pct: int, attempts: int}>,
     *     groups: Collection<int, array{title: string, pct: int, attempts: int}>,
     *     recent: Collection<int, Test>,
     * }
     */
    private function loadStats(): array
    {
        $counts = [
            'students' => User::where('role', Role::Student)->count(),
            'groups' => Group::count(),
            'subjects' => Subject::count(),
            'accesses' => TestAccess::count(),
        ];

        $summary = Test::whereNotNull('completed_at')
            ->selectRaw('
                COUNT(*) as total,
                ROUND(AVG(CASE WHEN max_score > 0 THEN total_score / max_score * 100 END)) as avg_pct
            ')
            ->first();

        $distributionRow = Test::whereNotNull('completed_at')
            ->where('max_score', '>', 0)
            ->selectRaw('
                SUM(CASE WHEN total_score / max_score * 100 >= 70 THEN 1 ELSE 0 END) as good,
                SUM(CASE WHEN total_score / max_score * 100 >= 50 AND total_score / max_score * 100 < 70 THEN 1 ELSE 0 END) as mid,
                SUM(CASE WHEN total_score / max_score * 100 < 50 THEN 1 ELSE 0 END) as low
            ')
            ->first();

        $days = collect(range(13, 0))->map(fn ($i) => now()->copy()->subDays($i)->startOfDay());

        $dailyCounts = Test::whereNotNull('completed_at')
            ->where('completed_at', '>=', $days->first())
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        $activity = $days->map(fn ($day) => [
            'label' => $day->translatedFormat('d.m'),
            'date' => $day->translatedFormat('d.m'),
            'count' => (int) ($dailyCounts[$day->format('Y-m-d')] ?? 0),
        ]);

        $subjects = TestSubject::query()
            ->join('tests', 'tests.id', '=', 'test_subjects.test_id')
            ->join('subjects', 'subjects.id', '=', 'test_subjects.subject_id')
            ->whereNotNull('tests.completed_at')
            ->groupBy('subjects.id', 'subjects.title')
            ->havingRaw('SUM(test_subjects.max_score) > 0')
            ->selectRaw('
                subjects.title as title,
                ROUND(SUM(test_subjects.score) / SUM(test_subjects.max_score) * 100) as pct,
                COUNT(*) as attempts
            ')
            ->orderByDesc('attempts')
            ->limit(6)
            ->get()
            ->map(fn ($row) => ['title' => $row->title, 'pct' => (int) $row->pct, 'attempts' => (int) $row->attempts]);

        $groups = Group::query()
            ->join('users', 'users.group_id', '=', 'groups.id')
            ->join('tests', 'tests.user_id', '=', 'users.id')
            ->whereNotNull('tests.completed_at')
            ->where('tests.max_score', '>', 0)
            ->groupBy('groups.id', 'groups.title')
            ->selectRaw('
                groups.title as title,
                ROUND(AVG(tests.total_score / tests.max_score * 100)) as pct,
                COUNT(*) as attempts
            ')
            ->orderByDesc('pct')
            ->limit(5)
            ->get()
            ->map(fn ($row) => ['title' => $row->title, 'pct' => (int) $row->pct, 'attempts' => (int) $row->attempts]);

        $recent = Test::whereNotNull('completed_at')
            ->with('user:id,name,login')
            ->latest('completed_at')
            ->limit(8)
            ->get(['id', 'user_id', 'total_score', 'max_score', 'completed_at']);

        return [
            'counts' => $counts,
            'totalCompleted' => (int) ($summary->total ?? 0),
            'avgPct' => (int) ($summary->avg_pct ?? 0),
            'distribution' => [
                'good' => (int) ($distributionRow->good ?? 0),
                'mid' => (int) ($distributionRow->mid ?? 0),
                'low' => (int) ($distributionRow->low ?? 0),
            ],
            'activity' => $activity,
            'subjects' => $subjects,
            'groups' => $groups,
            'recent' => $recent,
        ];
    }
}
