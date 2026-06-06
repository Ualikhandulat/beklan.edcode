<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Test;
use App\Models\TestAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): RedirectResponse
    {
        return redirect()->route('student.tests.active');
    }

    public function active(): View
    {
        $user = auth()->user()->load('group');

        $allAccesses = TestAccess::forUser($user->id)
            ->with(['accessSubjects.subject'])
            ->latest()
            ->get()
            ->filter(function (TestAccess $access) use ($user) {
                if ($access->attempts_limit === 0) {
                    return true;
                }

                $completed = Test::where('test_access_id', $access->id)
                    ->where('user_id', $user->id)
                    ->whereNotNull('completed_at')
                    ->count();

                return $completed < $access->attempts_limit;
            })
            ->values();

        $inProgressTests = Test::whereIn('test_access_id', $allAccesses->pluck('id'))
            ->where('user_id', $user->id)
            ->whereNull('completed_at')
            ->get()
            ->keyBy('test_access_id');

        $inProgress = $allAccesses->filter(fn ($a) => $inProgressTests->has($a->id))->values();
        $available = $allAccesses->filter(fn ($a) => ! $inProgressTests->has($a->id))->values();
        $hasActiveTest = $inProgress->isNotEmpty();

        return view('student.tests.active', compact('user', 'inProgress', 'available', 'hasActiveTest', 'inProgressTests'));
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
