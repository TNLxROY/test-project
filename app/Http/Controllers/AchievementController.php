<?php

namespace App\Http\Controllers;

use App\Services\AchievementService;
use Illuminate\Support\Facades\Auth;

class AchievementController extends Controller
{
    public function index()
    {
        $service      = app(AchievementService::class);
        $achievements = $service->forUser(Auth::user());
        $earned       = collect($achievements)->where('earned', true)->count();
        $total        = count($achievements);
        $titleMap     = $service->titleMap();

        return view('achievements.index', compact('achievements', 'earned', 'total', 'titleMap'));
    }
}
