<?php

namespace App\Http\Controllers\Public;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Subject;
use App\Models\TestAccess;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $subjects = Subject::query()
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title']);

        $stats = [
            'students' => User::where('role', Role::Student)->count(),
            'subjects' => $subjects->count(),
            'groups' => Group::count(),
        ];

        $trialAccess = TestAccess::query()
            ->where('is_trial', true)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->with('accessSubjects.subject')
            ->latest()
            ->first();

        $trialSubject = $trialAccess?->accessSubjects->first()?->subject;
        $trialPart = $trialAccess?->accessSubjects->first()?->part;
        $trialQuestionCount = $trialPart?->questions()->count() ?: ($trialAccess?->question_count ?: null);

        return view('public.home', compact('subjects', 'stats', 'trialAccess', 'trialSubject', 'trialQuestionCount'));
    }
}
