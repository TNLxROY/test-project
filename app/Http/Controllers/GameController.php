<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RawgService;
use Illuminate\Support\Facades\Cache;

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
        $game = $this->rawg->getGame($id);

        return view('games.show', compact('game'));
    }
}
