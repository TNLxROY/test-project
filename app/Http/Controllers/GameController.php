<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RawgService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http ;

class GameController extends Controller
{
    protected $rawg;

    public function __construct(RawgService $rawg)
    {
        $this->rawg = $rawg;
    }

    public function index()
    {
        $response = $this->rawg->getPopularGames();

        $games = collect($response['results'] ?? [])
            ->map(function ($game) {

                $details = Cache::remember("rawg_game_{$game['id']}", 3600, function () use ($game) {
                    return $this->rawg->getGame($game['id']);
                });

                $game['developers'] = $details['developers'] ?? [];

                $game['is_adult'] = false;

                /* ONLY hard ESRB block */
                $rating = $details['esrb_rating']['name'] ?? null;

                if ($rating === 'Adults Only') {
                    $game['is_adult'] = true;
                }

                /* ONLY explicit NSFW keywords */
                $nsfwKeywords = [
                    'hentai',
                    'porn',
                    'erotic hentai',
                    'explicit sexual content'
                ];

                foreach ($details['tags'] ?? [] as $tag) {
                    $name = strtolower($tag['name'] ?? '');

                    foreach ($nsfwKeywords as $keyword) {
                        if (str_contains($name, $keyword)) {
                            $game['is_adult'] = true;
                            break 2;
                        }
                    }
                }

                /* STRICT title filter */
                $title = strtolower($details['name'] ?? '');

                foreach (['hentai', 'porn'] as $word) {
                    if (str_contains($title, $word)) {
                        $game['is_adult'] = true;
                        break;
                    }
                }

                return $game;
            });

        return view('games.index', [
            'games' => $games,
        ]);

    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return redirect()->route('games.index');
        }

        $response = $this->rawg->searchGames($query);

        $games = collect($response['results'] ?? [])
            ->map(function ($game) {

                $details = Cache::remember("game_{$game['id']}", 3600, function () use ($game) {
                return $this->rawg->getGame($game['id']);
                });

                $game['developers'] = $details['developers'] ?? [];

                return $game;
            });

        return view('games.index', [
            'games' => $games,
            'query' => $query
    ]);
}

    public function show($id)
    {
        $apiKey = env('RAWG_API_KEY');

        $response = Http::get("https://api.rawg.io/api/games/{$id}", [
            'key' => $apiKey,
        ]);

        if ($response->failed()) {
            abort(404, 'Game not found.');
        }

        $game = $response->json();

        // RAWG stores
        $stores    = $this->rawg->getGameStores($id);
        $storeUrls = collect($stores['results'] ?? [])
            ->keyBy('store_id')
            ->map(fn($s) => $s['url']);

        if (!empty($game['stores'])) {
            $game['stores'] = array_map(function ($s) use ($storeUrls) {
                $s['url'] = $storeUrls[$s['store']['id']] ?? null;
                return $s;
            }, $game['stores']);
        }

        // RAWG screenshots
        $screenshotsRes = Http::get("https://api.rawg.io/api/games/{$id}/screenshots", [
            'key' => $apiKey,
        ]);
        $screenshots = $screenshotsRes->json()['results'] ?? [];

        // RAWG achievements
        $achievementsRes = Http::get("https://api.rawg.io/api/games/{$id}/achievements", [
            'key' => $apiKey,
        ]);
        $achievements = $achievementsRes->json()['results'] ?? [];

        // RAWG suggested games
        $suggestedRes = Http::get("https://api.rawg.io/api/games/{$id}/suggested", [
            'key' => $apiKey,
        ]);
        $suggested = $suggestedRes->json()['results'] ?? [];

        // RAWG game series
        $seriesRes = Http::get("https://api.rawg.io/api/games/{$id}/game-series", [
            'key' => $apiKey,
        ]);
        $gameSeries = $seriesRes->json()['results'] ?? [];

        // IGDB
        $igdb     = app(\App\Services\IgdbService::class);
        $igdbGame = Cache::remember("igdb_game_{$id}", 3600 * 6, function () use ($igdb, $game) {
            return $igdb->searchGame($game['name']);
        });

        $igdbCoverUrl = null;
        if (!empty($igdbGame['id'])) {
            $igdbCoverUrl = Cache::remember("igdb_cover_{$igdbGame['id']}", 3600 * 24, function () use ($igdb, $igdbGame) {
                return $igdb->getGameCover($igdbGame['id']);
            });
        }

        $igdbPlatforms = [];
        if (!empty($igdbGame['id'])) {
            $igdbPlatforms = Cache::remember("igdb_platforms_{$igdbGame['id']}", 3600 * 24, function () use ($igdb, $igdbGame) {
                return $igdb->getGamePlatforms($igdbGame['id']);
            });
        }

        $igdbCharacters = [];
        if (!empty($igdbGame['id'])) {
            $igdbCharacters = Cache::remember("igdb_chars_{$igdbGame['id']}", 3600 * 6, function () use ($igdb, $igdbGame) {
                return $igdb->getCharacters($igdbGame['id']);
            });
        }

        // reviews
        $reviews = \App\Models\Review::where('game_id', $id)
            ->with(['user', 'votes'])
            ->latest()
            ->get();

        $userReview = auth()->check()
            ? $reviews->firstWhere('user_id', auth()->id())
            : null;

        return view('games.show', compact(
            'game', 'reviews', 'userReview',
            'screenshots', 'achievements', 'suggested', 'gameSeries',
            'igdbGame', 'igdbCharacters', 'igdbCoverUrl', 'igdbPlatforms'
        ));
    }
}
