<?php

namespace App\Listeners;

use App\Events\ReviewCreated;
use App\Services\XpService;

class AwardReviewXp
{
    public function __construct(
        private readonly XpService $xpService,
    ) {}

    public function handle(ReviewCreated $event): void
    {
        $this->xpService->awardReviewXp($event->user);
    }
}
