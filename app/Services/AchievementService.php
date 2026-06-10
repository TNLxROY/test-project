<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserAchievement;

class AchievementService
{
    /**
     * All achievements defined here.
     * key        => unique identifier stored in DB
     * title      => display name
     * desc       => description shown to user
     * icon       => tabler icon class
     * color      => accent color
     * secret     => if true, shows as ??? until earned
     * condition  => callable that receives User and returns bool
     */
    public function all(): array
    {
        return [
            // ── Review achievements ──────────────────────────
            'first_review' => [
                'key'       => 'first_review',
                'title'     => 'First Words',
                'desc'      => 'Write your first game review.',
                'icon'      => 'ti-pencil',
                'color'     => '#e8192c',
                'secret'    => false,
                'condition' => fn(User $u) => $u->reviews()->count() >= 1,
            ],
            'review_5' => [
                'key'       => 'review_5',
                'title'     => 'Critic',
                'desc'      => 'Write 5 game reviews.',
                'icon'      => 'ti-writing',
                'color'     => '#ff4455',
                'secret'    => false,
                'condition' => fn(User $u) => $u->reviews()->count() >= 5,
            ],
            'review_10' => [
                'key'       => 'review_10',
                'title'     => 'Seasoned Reviewer',
                'desc'      => 'Write 10 game reviews.',
                'icon'      => 'ti-award',
                'color'     => '#f59e0b',
                'secret'    => false,
                'condition' => fn(User $u) => $u->reviews()->count() >= 10,
            ],
            'review_25' => [
                'key'       => 'review_25',
                'title'     => 'Fact Speaker',
                'desc'      => 'Write 25 game reviews.',
                'icon'      => 'ti-medal',
                'color'     => '#f59e0b',
                'secret'    => false,
                'condition' => fn(User $u) => $u->reviews()->count() >= 25,
            ],
            'review_50' => [
                'key'       => 'review_50',
                'title'     => 'Legendary Critic',
                'desc'      => 'Write 50 game reviews.',
                'icon'      => 'ti-trophy',
                'color'     => '#fbbf24',
                'secret'    => true,
                'condition' => fn(User $u) => $u->reviews()->count() >= 50,
            ],

            // ── Social achievements ──────────────────────────
            'first_friend' => [
                'key'       => 'first_friend',
                'title'     => 'Making Friends',
                'desc'      => 'Add your first friend.',
                'icon'      => 'ti-users',
                'color'     => '#4fc3f7',
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()->count() >= 1,
            ],
            'friends_5' => [
                'key'       => 'friends_5',
                'title'     => 'Social Gamer',
                'desc'      => 'Have 5 friends on your list.',
                'icon'      => 'ti-user-plus',
                'color'     => '#4fc3f7',
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()->count() >= 5,
            ],
            'friends_10' => [
                'key'       => 'friends_10',
                'title'     => 'Community Pillar',
                'desc'      => 'Have 10 friends on your list.',
                'icon'      => 'ti-heart',
                'color'     => '#e8192c',
                'secret'    => true,
                'condition' => fn(User $u) => $u->friends()->count() >= 10,
            ],

            // ── Profile achievements ─────────────────────────
            'avatar_set' => [
                'key'       => 'avatar_set',
                'title'     => 'Face of the Community',
                'desc'      => 'Upload a profile picture.',
                'icon'      => 'ti-camera',
                'color'     => '#a5d6a7',
                'secret'    => false,
                'condition' => fn(User $u) => !empty($u->avatar),
            ],

            // ── Secret achievements ──────────────────────────
            'early_adopter' => [
                'key'       => 'early_adopter',
                'title'     => 'Early Adopter',
                'desc'      => 'One of the first 100 users to join.',
                'icon'      => 'ti-rocket',
                'color'     => '#818cf8',
                'secret'    => true,
                'condition' => fn(User $u) => $u->id <= 100,
            ],
        ];
    }

    /**
     * Maps achievement keys to the title label they grant.
     * Add an entry here whenever an achievement should unlock a title.
     */
    public function titleMap(): array
    {
        return [
            'first_review'  => 'First Words',
            'review_5'      => 'Critic',
            'review_10'     => 'Seasoned Reviewer',
            'review_25'     => 'Fact Speaker',
            'review_50'     => 'Legendary Critic',
            'first_friend'  => 'Making Friends',
            'friends_5'     => 'Social Gamer',
            'friends_10'    => 'Community Pillar',
            'avatar_set'    => 'Face of the Community',
            'early_adopter' => 'Early Adopter',
        ];
    }

    /**
     * Returns all titles with unlocked status for a user.
     * Each entry: [label, achievement, unlocked, secret]
     */
    public function titlesForUser(User $user): array
    {
        $earned = UserAchievement::where('user_id', $user->id)
                    ->pluck('achievement_key')
                    ->toArray();

        $achievements = $this->all();
        $map          = $this->titleMap();
        $titles       = [];

        foreach ($map as $achievementKey => $titleLabel) {
            $achievement = $achievements[$achievementKey] ?? null;
            if (!$achievement) continue;

            $titles[] = [
                'label'       => $titleLabel,
                'achievement' => $achievement['title'],
                'unlocked'    => in_array($achievementKey, $earned),
                'secret'      => $achievement['secret'] ?? false,
            ];
        }

        return $titles;
    }

    /**
     * Check and award any newly earned achievements for a user.
     * Returns array of newly awarded achievement keys.
     */
    public function checkAndAward(User $user): array
    {
        $earned = UserAchievement::where('user_id', $user->id)
                    ->pluck('achievement_key')
                    ->toArray();

        $newlyEarned = [];

        foreach ($this->all() as $key => $achievement) {
            if (in_array($key, $earned)) continue;

            if (($achievement['condition'])($user)) {
                UserAchievement::create([
                    'user_id'         => $user->id,
                    'achievement_key' => $key,
                    'earned_at'       => now(),
                ]);
                $newlyEarned[] = $key;
            }
        }

        return $newlyEarned;
    }

    /**
     * Get all achievements with earned status for a user.
     */
    public function forUser(User $user): array
    {
        $earned = UserAchievement::where('user_id', $user->id)
                    ->get()
                    ->keyBy('achievement_key');

        $result = [];

        foreach ($this->all() as $key => $achievement) {
            $earnedRecord = $earned->get($key);
            $result[]     = array_merge($achievement, [
                'earned'    => !is_null($earnedRecord),
                'earned_at' => $earnedRecord?->earned_at,
            ]);
        }

        return $result;
    }
}
