<?php

namespace App\Http\Controllers\Public;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Subject;
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

        return view('public.home', compact('subjects', 'stats'));
    }
}
