<?php

/**
 * Add this to your App\Providers\EventServiceProvider::$listen array.
 *
 * If you're on Laravel 11 with the new bootstrap/app.php approach and no
 * EventServiceProvider, register this inside AppServiceProvider::boot()
 * using Event::listen() instead — see the comment block at the bottom.
 */

// ── Option A: EventServiceProvider (Laravel 8–10) ───────────────────────────

namespace App\Providers;

use App\Events\ReviewCreated;
use App\Listeners\AwardReviewXp;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // ... your existing events ...

        ReviewCreated::class => [
            AwardReviewXp::class,
        ],
    ];
}


// ── Option B: AppServiceProvider::boot() (Laravel 11+) ──────────────────────
//
// use Illuminate\Support\Facades\Event;
// use App\Events\ReviewCreated;
// use App\Listeners\AwardReviewXp;
//
// public function boot(): void
// {
//     Event::listen(ReviewCreated::class, AwardReviewXp::class);
// }
