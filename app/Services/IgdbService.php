<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IgdbService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl = 'https://api.igdb.com/v4';

    public function __construct()
    {
        $this->clientId     = config('services.igdb.client_id');
        $this->clientSecret = config('services.igdb.client_secret');
    }

    protected function getAccessToken(): string
    {
        return Cache::remember('igdb_access_token', 3600 * 24 * 30, function () {
            $res = Http::post('https://id.twitch.tv/oauth2/token', [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'grant_type'    => 'client_credentials',
            ]);

            return $res->json()['access_token'];
        });
    }

    protected function request(string $endpoint, string $body): array
    {
        $token = $this->getAccessToken();

        $res = Http::withHeaders([
            'Client-ID'     => $this->clientId,
            'Authorization' => 'Bearer ' . $token,
            'Accept'        => 'application/json',
        ])->withBody($body, 'text/plain')
          ->post("{$this->baseUrl}/{$endpoint}");

        return $res->json() ?? [];
    }

    public function searchGame(string $name): ?array
    {
        $results = $this->request('games', "
            search \"{$name}\";
            fields id, name, summary, storyline,
                   cover.url,
                   artworks.url,
                   screenshots.url,
                   videos.video_id,
                   characters.name, characters.description, characters.mug_shot.url, characters.gender, characters.species,
                   involved_companies.company.name, involved_companies.developer, involved_companies.publisher,
                   genres.name,
                   themes.name,
                   game_modes.name,
                   player_perspectives.name,
                   age_ratings.category, age_ratings.rating,
                   websites.category, websites.url,
                   similar_games.name, similar_games.cover.url, similar_games.id,
                   first_release_date,
                   total_rating, total_rating_count,
                   franchise.name, franchises.name;
            limit 1;
        ");

        return $results[0] ?? null;
    }

    public function getCharacters(int $igdbId): array
    {
        $results = $this->request('characters', "
            fields name, description, mug_shot.url, gender, species, games.name;
            where games = ({$igdbId});
            limit 20;
        ");

        return $results ?? [];
    }
}
