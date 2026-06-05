<?php

namespace App\Http\Controllers;

use App\Services\AchievementService;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index()
    {
        $achievements = app(AchievementService::class)->forUser(Auth::user());
        $earned       = collect($achievements)->where('earned', true)->count();
        $total        = count($achievements);

        return view('achievements.index', compact('achievements', 'earned', 'total'));
    }
}
