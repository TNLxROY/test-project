<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\RawgService;

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

        $games = $response['results'] ?? [];

        return view('games.index', [
            'games' => $games
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return redirect()->route('games.index');
        }

        $response = $this->rawg->searchGames($query);

        $games = $response['results'] ?? [];

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
