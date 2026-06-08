<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class IgdbService
{
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl = 'https://api.igdb.com/v4';

    // Cache TTLs
    const TTL_TOKEN      = 3600 * 24 * 30; // 30 days  — token is long-lived
    const TTL_GAME       = 3600 * 24;       // 24 hours — game metadata is stable
    const TTL_COVER      = 3600 * 24 * 7;  // 7 days   — cover images never change
    const TTL_PLATFORMS  = 3600 * 24 * 7;  // 7 days
    const TTL_CHARACTERS = 3600 * 24;       // 24 hours
    const TTL_LOGOS      = 3600 * 24 * 7;  // 7 days

    public function __construct()
    {
        $this->clientId     = config('services.igdb.client_id');
        $this->clientSecret = config('services.igdb.client_secret');
    }

    protected function getAccessToken(): string
    {
        return Cache::remember('igdb_access_token', self::TTL_TOKEN, function () {
            $res = Http::timeout(8)->post('https://id.twitch.tv/oauth2/token', [
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

        $res = Http::timeout(8)
            ->withHeaders([
                'Client-ID'     => $this->clientId,
                'Authorization' => 'Bearer ' . $token,
                'Accept'        => 'application/json',
            ])
            ->withBody($body, 'text/plain')
            ->post("{$this->baseUrl}/{$endpoint}");

        return $res->json() ?? [];
    }

    public function searchGame(string $name): ?array
    {
        $key = 'igdb.search.' . md5($name);

        return Cache::remember($key, self::TTL_GAME, function () use ($name) {
            $results = $this->request('games', "
                search \"{$name}\";
                fields id, name, summary, storyline,
                    cover.url,
                    release_dates.platform.name, release_dates.human,
                    artworks.url,
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
        });
    }

    public function getCharacters(int $igdbId): array
    {
        $key = "igdb.characters.{$igdbId}";

        return Cache::remember($key, self::TTL_CHARACTERS, function () use ($igdbId) {
            return $this->request('characters', "
                fields name, description, mug_shot.url, gender, species, games.name;
                where games = ({$igdbId});
                limit 20;
            ") ?? [];
        });
    }

    public function getGameCover(int $igdbId): ?string
    {
        $key = "igdb.cover.{$igdbId}";

        return Cache::remember($key, self::TTL_COVER, function () use ($igdbId) {
            $results = $this->request('covers', "
                fields url, width, height, image_id;
                where game = {$igdbId};
                limit 1;
            ");

            if (!empty($results[0]['image_id'])) {
                return 'https://images.igdb.com/igdb/image/upload/t_1080p/' . $results[0]['image_id'] . '.jpg';
            }

            return null;
        });
    }

    public function getPlatformLogos(array $igdbPlatformIds): array
    {
        if (empty($igdbPlatformIds)) return [];

        sort($igdbPlatformIds); // normalize key order so [1,2] and [2,1] share a cache entry
        $key = 'igdb.platform_logos.' . md5(implode(',', $igdbPlatformIds));

        return Cache::remember($key, self::TTL_LOGOS, function () use ($igdbPlatformIds) {
            $ids = implode(',', $igdbPlatformIds);

            return $this->request('platform_logos', "
                fields alpha_channel, animated, checksum, height, image_id, trusted, url, width;
                where id = ({$ids});
                limit 50;
            ") ?? [];
        });
    }

    public function getGamePlatforms(int $igdbId): array
    {
        $key = "igdb.platforms.{$igdbId}";

        return Cache::remember($key, self::TTL_PLATFORMS, function () use ($igdbId) {
            $results = $this->request('games', "
                fields platforms.name, platforms.platform_logo.id, platforms.platform_logo.image_id, platforms.slug;
                where id = {$igdbId};
                limit 1;
            ");

            return $results[0]['platforms'] ?? [];
        });
    }

    /**
     * Fetch all data needed for a game show page in one go.
     *
     * Instead of making 4 sequential calls from the controller, call this
     * single method. On cache-miss the requests still go out sequentially,
     * but on cache-hit (all subsequent page loads) everything is served
     * from cache in a single pass with zero HTTP round-trips.
     *
     * Usage in your controller:
     *   $igdb = $this->igdbService->getShowPageData($igdbId, $gameName);
     *   // $igdb['game'], $igdb['cover'], $igdb['platforms'], $igdb['characters']
     */
    public function getShowPageData(int $igdbId, string $gameName): array
    {
        $key = "igdb.show_page.{$igdbId}";

        return Cache::remember($key, self::TTL_GAME, function () use ($igdbId, $gameName) {
            $game       = $this->searchGame($gameName);
            $cover      = $this->getGameCover($igdbId);
            $platforms  = $this->getGamePlatforms($igdbId);
            $characters = $this->getCharacters($igdbId);

            return compact('game', 'cover', 'platforms', 'characters');
        });
    }
}
