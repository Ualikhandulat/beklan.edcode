<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestAccess;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user()->load('group');

        $accesses = TestAccess::forUser($user->id)
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

        return view('student.dashboard', compact('user', 'inProgress', 'available', 'hasActiveTest', 'inProgressTests', 'completedCounts'));
    }

    public function history(): View
    {
        $user = auth()->user()->load('group');

        $tests = Test::where('user_id', $user->id)
            ->whereNotNull('completed_at')
            ->with(['access.accessSubjects.subject'])
            ->latest('completed_at')
            ->get();

        return view('student.tests.history', compact('user', 'tests'));
    }

    public function info(): View
    {
        return view('student.info');
    }
}
