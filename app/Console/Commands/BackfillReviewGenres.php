<?php

namespace App\Console\Commands;

use App\Models\Review;
use App\Services\RawgService;
use Illuminate\Console\Command;

class BackfillReviewGenres extends Command
{
    protected $signature = 'reviews:backfill-genres {--force : Re-fetch genres even for reviews that already have them}';

    protected $description = 'Populate the genres column on existing reviews using RAWG game data.';

    public function handle(RawgService $rawgService): int
    {
        $query = Review::query();

        if (!$this->option('force')) {
            $query->whereNull('genres');
        }

        $reviews = $query->get();

        if ($reviews->isEmpty()) {
            $this->info('Nothing to backfill — every review already has genre data.');
            return self::SUCCESS;
        }

        // Group by game_id so each game only hits RawgService once,
        // regardless of how many reviews exist for it.
        $byGame = $reviews->groupBy('game_id');

        $bar = $this->output->createProgressBar($byGame->count());
        $bar->start();

        $updated = 0;
        $failed  = [];

        foreach ($byGame as $gameId => $gameReviews) {
            try {
                $game   = $rawgService->getGame($gameId);
                $genres = $game['genres'] ?? [];

                Review::whereIn('id', $gameReviews->pluck('id'))
                    ->update(['genres' => json_encode($genres)]);

                $updated += $gameReviews->count();
            } catch (\Throwable $e) {
                $failed[] = $gameId;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Backfilled genres for {$updated} review(s) across {$byGame->count()} game(s).");

        if (!empty($failed)) {
            $this->warn('Could not fetch genre data for game IDs: ' . implode(', ', $failed));
        }

        return self::SUCCESS;
    }
}
