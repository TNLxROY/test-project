<?php

namespace App\Providers;

use App\Events\ReviewCreated;
use App\Listeners\AwardReviewXp;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        ReviewCreated::class => [
            AwardReviewXp::class,
        ],
    ];
}
