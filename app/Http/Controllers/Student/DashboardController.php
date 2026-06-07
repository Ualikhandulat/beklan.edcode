<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestAccess;
use App\Models\TestSubject;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user()->load('group');

        $accesses = TestAccess::forCurrentUser()
            ->with(['accessSubjects'])
            ->latest()
            ->get();

        $accessIds = $accesses->pluck('id');

        $completedCounts = Test::whereIn('test_access_id', $accessIds)
            ->where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->selectRaw('test_access_id, count(*) as completed_count')
            ->groupBy('test_access_id')
            ->pluck('completed_count', 'test_access_id')
            ->map(fn ($count) => (int) $count);

        $inProgressTests = Test::whereIn('test_access_id', $accessIds)
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->get()
            ->keyBy('test_access_id');

        $allAccesses = $accesses->filter(fn (TestAccess $access) => $access->attempts_limit === 0
            || $completedCounts->get($access->id, 0) < $access->attempts_limit
        )->values();

        $inProgress = $allAccesses->filter(fn ($a) => $inProgressTests->has($a->id))->values();
        $available = $allAccesses->filter(fn ($a) => ! $inProgressTests->has($a->id))->values();
        $hasActiveTest = $inProgress->isNotEmpty();

        $stats = $this->buildStats($user);

        return view('student.dashboard', compact('user', 'inProgress', 'available', 'hasActiveTest', 'inProgressTests', 'completedCounts', 'stats'));
    }

    /**
     * Build progress/activity statistics for the dashboard charts using a small,
     * fixed number of aggregate queries (no N+1).
     *
     * @return array{
     *     totalTests: int,
     *     avgPct: int,
     *     bestPct: int,
     *     progress: Collection<int, array{date: string, pct: int}>,
     *     subjects: Collection<int, array{title: string, pct: int, attempts: int}>,
     *     activity: Collection<int, array{label: string, date: string, count: int}>,
     * }
     */
    private function buildStats(User $user): array
    {
        // Cache key format mirrored in TestAssemblyService::score(), which forgets it on test completion.
        // Cached as plain arrays (the app forbids serializing objects, see config/cache.php
        // `serializable_classes`), then re-wrapped into Collections for the view.
        $cached = Cache::remember("student.dashboard.stats.{$user->id}", now()->addDays(7), fn () => $this->loadStats($user));

        return [
            ...$cached,
            'progress' => collect($cached['progress']),
            'subjects' => collect($cached['subjects']),
            'activity' => collect($cached['activity']),
        ];
    }

    /** @return array<string, mixed> plain, cache-serializable data (no objects) */
    private function loadStats(User $user): array
    {
        $summary = Test::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->selectRaw('
                COUNT(*) as total_tests,
                ROUND(AVG(CASE WHEN max_score > 0 THEN total_score / max_score * 100 END)) as avg_pct,
                ROUND(MAX(CASE WHEN max_score > 0 THEN total_score / max_score * 100 END)) as best_pct
            ')
            ->first();

        $progress = Test::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->where('max_score', '>', 0)
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get(['completed_at', 'total_score', 'max_score'])
            ->reverse()
            ->values()
            ->map(fn (Test $test) => [
                'date' => $test->completed_at->format('d.m'),
                'pct' => (int) round($test->total_score / $test->max_score * 100),
            ])
            ->all();

        $subjects = TestSubject::query()
            ->join('tests', 'tests.id', '=', 'test_subjects.test_id')
            ->join('subjects', 'subjects.id', '=', 'test_subjects.subject_id')
            ->where('tests.user_id', $user->id)
            ->whereNotNull('tests.completed_at')
            ->groupBy('subjects.id', 'subjects.title')
            ->havingRaw('SUM(test_subjects.max_score) > 0')
            ->selectRaw('
                subjects.title as title,
                ROUND(SUM(test_subjects.score) / SUM(test_subjects.max_score) * 100) as pct,
                COUNT(*) as attempts,
                SUM(test_subjects.max_score) as total_max
            ')
            ->orderByDesc('total_max')
            ->limit(6)
            ->get()
            ->map(fn ($row) => [
                'title' => $row->title,
                'pct' => (int) $row->pct,
                'attempts' => (int) $row->attempts,
            ])
            ->all();

        $days = collect(range(13, 0))->map(fn ($i) => now()->copy()->subDays($i)->startOfDay());

        $dailyCounts = Test::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $days->first())
            ->selectRaw('DATE(completed_at) as day, COUNT(*) as cnt')
            ->groupBy('day')
            ->pluck('cnt', 'day');

        $activity = $days->map(fn ($day) => [
            'label' => $day->format('j'),
            'date' => $day->format('d.m'),
            'count' => (int) ($dailyCounts[$day->format('Y-m-d')] ?? 0),
        ])->all();

        return [
            'totalTests' => (int) ($summary->total_tests ?? 0),
            'avgPct' => (int) ($summary->avg_pct ?? 0),
            'bestPct' => (int) ($summary->best_pct ?? 0),
            'progress' => $progress,
            'subjects' => $subjects,
            'activity' => $activity,
        ];
    }

    public function history(): View
    {
        $user = auth()->user()->load('group');

        $tests = Test::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with(['access.accessSubjects.subject'])
            ->latest('completed_at')
            ->paginate()
            ->withQueryString();

        return view('student.tests.history', compact('user', 'tests'));
    }

    public function info(): View
    {
        return view('student.info');
    }
}
