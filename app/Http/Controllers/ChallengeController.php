<?php

namespace App\Http\Controllers;

use App\Models\Ruleset;
use App\Services\RawgService;
use Illuminate\Http\Request;

class ChallengeController extends Controller
{
    public function __construct(protected RawgService $rawg) {}

    /*
     * GET /challenges
     * Search landing page — shows popular games as a starting grid.
     */
    public function index()
    {
        $popular = $this->rawg->getPopularGames();

        $popularGames = collect($popular['results'] ?? [])
            ->take(12)
            ->map(fn ($g) => [
                'id'               => $g['id'],
                'name'             => $g['name'],
                'background_image' => $g['background_image'] ?? null,
                'rating'           => $g['rating']           ?? null,
                'released'         => $g['released']         ?? null,
                'genres'           => $g['genres']           ?? [],
            ])
            ->values()
            ->all();

        return view('challenges.index', compact('popularGames'));
    }

    /*
     * GET /challenges/game/{rawgId}
     * Shows all public rulesets for a specific game.
     */
    public function game(int $rawgId)
    {
        // Pull game data from RAWG (cached 24h via RawgService)
        $game = $this->rawg->getGame($rawgId);

        if (empty($game['id'])) {
            abort(404, 'Game not found.');
        }

        $rulesets = Ruleset::with('user')
            ->where('rawg_id', $rawgId)
            ->where('is_public', true)
            ->latest()
            ->paginate(10);

        return view('challenges.game', compact('game', 'rulesets'));
    }
}
