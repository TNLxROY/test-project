<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class RawgService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.rawg.base_url');
        $this->apiKey = config('services.rawg.key');
    }

    protected function request($endpoint, $params = [])
    {
        $params['key'] = $this->apiKey;

        return Http::get("{$this->baseUrl}/{$endpoint}", $params)->json();
    }

    // LISTS (ALWAYS RAW FORMAT)
    public function searchGames($query)
    {
        return $this->request('games', [
            'search' => $query,
            'exclude_additions' => true,
        ]);
    }

    public function getPopularGames()
    {
        return $this->request('games', [
            'ordering' => '-rating',
        ]);
    }

    // SINGLE GAME (ALWAYS NORMALIZED)
    public function getGame($id)
    {
        $data = $this->request("games/{$id}");

        return [
            'id' => $data['id'] ?? null,
            'name' => $data['name'] ?? '',
            'image' => $data['image'] ?? null,
            'rating' => $data['rating'] ?? null,
            'released' => $data['released'] ?? null,

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
    }

    public function getGameStores(int|string $id): array
    {
        return $this->request("games/{$id}/stores") ?? [];
    }
}
