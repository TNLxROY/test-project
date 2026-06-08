<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RawgService
{
    protected $baseUrl;
    protected $apiKey;

    // How long to cache each type of response
    const TTL_POPULAR  = 3600;   // popular games: 1 hour  (changes rarely)
    const TTL_SEARCH   = 300;    // search results: 5 min  (user-specific, keep fresh)
    const TTL_GAME     = 86400;  // single game detail: 24 hours (very stable data)
    const TTL_STORES   = 86400;  // store links: 24 hours

    public function __construct()
    {
        $this->baseUrl = config('services.rawg.base_url');
        $this->apiKey  = config('services.rawg.key');
    }

    protected function request($endpoint, $params = [])
    {
        $params['key'] = $this->apiKey;

        return Http::timeout(8)
            ->get("{$this->baseUrl}/{$endpoint}", $params)
            ->json();
    }

    // LISTS (ALWAYS RAW FORMAT)
    public function searchGames($query)
    {
        $key = 'rawg.search.' . md5($query);

        return Cache::remember($key, self::TTL_SEARCH, function () use ($query) {
            return $this->request('games', [
                'search'            => $query,
                'exclude_additions' => true,
            ]);
        });
    }

    public function getPopularGames()
    {
        return Cache::remember('rawg.popular', self::TTL_POPULAR, function () {
            return $this->request('games', [
                'ordering' => '-rating',
            ]);
        });
    }

    // SINGLE GAME (ALWAYS NORMALIZED)
    public function getGame($id)
    {
        $key = "rawg.game.{$id}";

        return Cache::remember($key, self::TTL_GAME, function () use ($id) {
            $data = $this->request("games/{$id}");

            return [
                'id'         => $data['id']               ?? null,
                'name'       => $data['name']              ?? '',
                // RAWG returns 'background_image', not 'image'
                'background_image' => $data['background_image'] ?? null,
                'rating'     => $data['rating']            ?? null,
                'released'   => $data['released']          ?? null,
                'genres'     => $data['genres']            ?? [],

                'developers' => collect($data['developers'] ?? [])
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->all(),

                'publishers' => collect($data['publishers'] ?? [])
                    ->pluck('name')
                    ->filter()
                    ->values()
                    ->all(),
            ];
        });
    }

    public function getGameStores(int|string $id): array
    {
        $key = "rawg.stores.{$id}";

        return Cache::remember($key, self::TTL_STORES, function () use ($id) {
            return $this->request("games/{$id}/stores") ?? [];
        });
    }
}
