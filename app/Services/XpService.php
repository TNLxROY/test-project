<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserLevel;

class XpService
{
    /** XP awarded for each action type. Easy to extend later. */
    private const XP_REWARDS = [
        'review' => 20,
    ];

    /**
     * Award XP for writing a review.
     * Returns the updated UserLevel and whether a level-up occurred.
     *
     * @return array{userLevel: UserLevel, leveledUp: bool, newLevel: int}
     */
    public function awardReviewXp(User $user): array
    {
        return $this->award($user, 'review');
    }

    /**
     * Generic XP award method. Add more action types here in the future.
     *
     * @return array{userLevel: UserLevel, leveledUp: bool, newLevel: int}
     */
    public function award(User $user, string $action): array
    {
        $xpAmount = self::XP_REWARDS[$action] ?? 0;

        if ($xpAmount === 0) {
            $userLevel = $this->getOrCreate($user);
            return ['userLevel' => $userLevel, 'leveledUp' => false, 'newLevel' => $userLevel->level];
        }

        $userLevel = $this->getOrCreate($user);
        $leveledUp = false;
        $oldLevel  = $userLevel->level;

        // Increment review count if this is a review action
        if ($action === 'review') {
            $userLevel->review_count++;
        }

        $userLevel->total_xp += $xpAmount;
        $userLevel->xp       += $xpAmount;

        // Process level-ups (handles multiple level-ups from a single XP grant)
        while ($userLevel->xp >= $userLevel->xpForCurrentLevel()) {
            $userLevel->xp    -= $userLevel->xpForCurrentLevel();
            $userLevel->level += 1;
            $leveledUp         = true;
        }

        $userLevel->save();

        return [
            'userLevel' => $userLevel,
            'leveledUp' => $leveledUp,
            'newLevel'  => $userLevel->level,
            'oldLevel'  => $oldLevel,
        ];
    }

    /**
     * Get the UserLevel record for a user, creating it if it doesn't exist.
     */
    public function getOrCreate(User $user): UserLevel
    {
        return UserLevel::firstOrCreate(
            ['user_id' => $user->id],
            ['level' => 1, 'xp' => 0, 'total_xp' => 0, 'review_count' => 0]
        );
    }
}
