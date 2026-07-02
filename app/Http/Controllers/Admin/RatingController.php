<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\LeaderboardService;
use Illuminate\View\View;

class RatingController extends Controller
{
    public function __construct(public LeaderboardService $leaderboard) {}

    public function index(): View
    {
        $rows = $this->leaderboard->query()
            ->paginate(20)
            ->withQueryString();

        $navigations = [route('admin.rating.index') => 'Рейтинг'];

        return view('admin.rating.index', compact('rows', 'navigations'));
    }
}
