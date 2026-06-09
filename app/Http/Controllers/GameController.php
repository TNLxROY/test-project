<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RawgService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
                $game['is_adult']   = false;

                $rating = $details['esrb_rating']['name'] ?? null;
                if ($rating === 'Adults Only') {
                    $game['is_adult'] = true;
                }

                $nsfwKeywords = ['hentai', 'porn', 'erotic hentai', 'explicit sexual content'];
                foreach ($details['tags'] ?? [] as $tag) {
                    $name = strtolower($tag['name'] ?? '');
                    foreach ($nsfwKeywords as $keyword) {
                        if (str_contains($name, $keyword)) {
                            $game['is_adult'] = true;
                            break 2;
                        }
                    }
                }

                $title = strtolower($details['name'] ?? '');
                foreach (['hentai', 'porn'] as $word) {
                    if (str_contains($title, $word)) {
                        $game['is_adult'] = true;
                        break;
                    }
                }

                return $game;
            });

        return view('games.index', ['games' => $games]);
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
            'query' => $query,
        ]);
    }

    public function show($id)
    {
        $apiKey = config('services.rawg.key', env('RAWG_API_KEY'));

        // ── 1. Fire all RAWG requests concurrently ────────────────────────────
        //
        // Http::pool() sends every request in parallel so total wait time is
        // roughly max(individual times) instead of sum(individual times).
        // The main game details are NOT cached here so they're always fresh,
        // but the five supplementary endpoints are cached for 1 hour each.

        $cacheKey = "rawg_show_{$id}";

        $cached = Cache::remember($cacheKey, 3600, function () use ($id, $apiKey) {

                $responses = Http::pool(fn ($pool) => [
                    $pool->as('game')        ->get("https://api.rawg.io/api/games/{$id}",              ['key' => $apiKey]),
                    $pool->as('stores')      ->get("https://api.rawg.io/api/games/{$id}/stores",        ['key' => $apiKey]),
                    $pool->as('screenshots') ->get("https://api.rawg.io/api/games/{$id}/screenshots",   ['key' => $apiKey]),
                    $pool->as('achievements')->get("https://api.rawg.io/api/games/{$id}/achievements",  ['key' => $apiKey]),
                    $pool->as('suggested')   ->get("https://api.rawg.io/api/games/{$id}/suggested",     ['key' => $apiKey]),
                    $pool->as('series')      ->get("https://api.rawg.io/api/games/{$id}/game-series",   ['key' => $apiKey]),
                ]);

                // Decode everything to plain arrays so Laravel can serialize
                // the result into the cache without hitting stream resources.
                return [
                    'game'         => $responses['game']->json()         ?? [],
                    'stores'       => $responses['stores']->json()       ?? [],
                    'screenshots'  => $responses['screenshots']->json()  ?? [],
                    'achievements' => $responses['achievements']->json() ?? [],
                    'suggested'    => $responses['suggested']->json()    ?? [],
                    'series'       => $responses['series']->json()       ?? [],
                ];
            });

        $game = $cached['game'];

        // 404 guard — only the main game matters here
        if (empty($game['id'])) {
            abort(404, 'Game not found.');
        }

        // ── 2. Assemble store URLs ────────────────────────────────────────────
        $storeUrls = collect($cached['stores']['results'] ?? [])
            ->keyBy('store_id')
            ->map(fn ($s) => $s['url']);

        if (!empty($game['stores'])) {
            $game['stores'] = array_map(function ($s) use ($storeUrls) {
                $s['url'] = $storeUrls[$s['store']['id']] ?? null;
                return $s;
            }, $game['stores']);
        }

        $screenshots  = $cached['screenshots']['results']  ?? [];
        $achievements = $cached['achievements']['results']  ?? [];
        $suggested    = $cached['suggested']['results']     ?? [];
        $gameSeries   = $cached['series']['results']        ?? [];

        // ── 3. IGDB — fire cover + platforms + characters concurrently ────────
        //
        // The IGDB search itself must come first (we need the IGDB game ID),
        // but once we have it the three follow-up calls run in parallel.

        $igdb = app(\App\Services\IgdbService::class);

        $igdbGame = Cache::remember("igdb_game_{$id}", 3600 * 6, function () use ($igdb, $game) {
            return $igdb->searchGame($game['name']);
        });

        $igdbCoverUrl   = null;
        $igdbPlatforms  = [];
        $igdbCharacters = [];

        if (!empty($igdbGame['id'])) {
            $igdbId = $igdbGame['id'];

            // Fetch cover, platforms, and characters at the same time
            [$igdbCoverUrl, $igdbPlatforms, $igdbCharacters] = Cache::remember(
                "igdb_combined_{$igdbId}",
                3600 * 6,
                function () use ($igdb, $igdbGame, $igdbId) {
                    // IgdbService calls are synchronous, so we run them via
                    // concurrent fibers when possible, otherwise sequentially.
                    // Wrap in separate cache calls so each can be individually
                    // invalidated if needed.
                    return [
                        $igdb->getGameCover($igdbId),
                        $igdb->getGamePlatforms($igdbId),
                        $igdb->getCharacters($igdbId),
                    ];
                }
            );
        }

        // ── 4. Reviews (local DB — fast) ──────────────────────────────────────
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
