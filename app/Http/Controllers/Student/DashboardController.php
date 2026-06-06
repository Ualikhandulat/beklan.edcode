<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TestAccess;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user()->load('group');

        $accesses = TestAccess::forUser($user->id)
            ->with(['accessSubjects.subject'])
            ->latest()
            ->get();

        return view('student.dashboard', compact('user', 'accesses'));
    }
}
