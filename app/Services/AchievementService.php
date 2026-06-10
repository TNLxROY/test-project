<?php

namespace App\Services;

use App\Models\ReviewVote;
use App\Models\User;
use App\Models\UserAchievement;
use App\Models\UserLevel;

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
                'secret'    => false,
                'condition' => fn(User $u) => $u->reviews()->count() >= 50,
            ],
            'review_100' => [
                'key'       => 'review_100',
                'title'     => 'John Review',
                'desc'      => 'Write 100 game reviews.',
                'icon'      => 'ti-trophy',
                'color'     => '#fbbf24',
                'secret'    => false,
                'condition' => fn(User $u) => $u->reviews()->count() >= 100,
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
                'title'     => 'In A Party',
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
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()->count() >= 10,
            ],
            'friends_151' => [
                'key'       => 'friends_151',
                'title'     => 'Gotta Friend \'Em All',
                'desc'      => 'Have 151 friends on your list.',
                'icon'      => 'ti-heart',
                'color'     => '#e8192c',
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()->count() >= 151,
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

            // ── Total likes received (across all reviews) ───
            'likes_1' => [
                'key'       => 'likes_1',
                'title'     => 'Crowd Pleaser',
                'desc'      => 'Receive your first like on a review.',
                'icon'      => 'ti-thumb-up',
                'color'     => '#4ade80',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', 1)->count() >= 1,
            ],
            'likes_10' => [
                'key'       => 'likes_10',
                'title'     => 'Well Received',
                'desc'      => 'Receive 10 likes across all your reviews.',
                'icon'      => 'ti-thumb-up',
                'color'     => '#22c55e',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', 1)->count() >= 10,
            ],
            'likes_50' => [
                'key'       => 'likes_50',
                'title'     => 'Fan Favourite',
                'desc'      => 'Receive 50 likes across all your reviews.',
                'icon'      => 'ti-star',
                'color'     => '#16a34a',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', 1)->count() >= 50,
            ],
            'likes_100' => [
                'key'       => 'likes_100',
                'title'     => 'Voice of the People',
                'desc'      => 'Receive 100 likes across all your reviews.',
                'icon'      => 'ti-crown',
                'color'     => '#15803d',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', 1)->count() >= 100,
            ],

            // ── Total dislikes received (across all reviews) ─
            'dislikes_1' => [
                'key'       => 'dislikes_1',
                'title'     => 'Controversial Take',
                'desc'      => 'Receive your first dislike on a review.',
                'icon'      => 'ti-thumb-down',
                'color'     => '#f87171',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', -1)->count() >= 1,
            ],
            'dislikes_10' => [
                'key'       => 'dislikes_10',
                'title'     => 'Unpopular Opinion',
                'desc'      => 'Receive 10 dislikes across all your reviews.',
                'icon'      => 'ti-mood-sad',
                'color'     => '#ef4444',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', -1)->count() >= 10,
            ],
            'dislikes_50' => [
                'key'       => 'dislikes_50',
                'title'     => 'Hated by Many',
                'desc'      => 'Receive 50 dislikes across all your reviews.',
                'icon'      => 'ti-flame',
                'color'     => '#dc2626',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', -1)->count() >= 50,
            ],

            // ── Single-post like achievements ────────────────
            'post_likes_5' => [
                'key'       => 'post_likes_5',
                'title'     => 'Hit Review',
                'desc'      => 'Get 5 likes on a single review.',
                'icon'      => 'ti-heart',
                'color'     => '#34d399',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', 1)
                                    ->selectRaw('review_id, COUNT(*) as total')
                                    ->groupBy('review_id')
                                    ->havingRaw('total >= 5')
                                    ->exists(),
            ],
            'post_likes_25' => [
                'key'       => 'post_likes_25',
                'title'     => 'Viral Review',
                'desc'      => 'Get 25 likes on a single review.',
                'icon'      => 'ti-trending-up',
                'color'     => '#10b981',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', 1)
                                    ->selectRaw('review_id, COUNT(*) as total')
                                    ->groupBy('review_id')
                                    ->havingRaw('total >= 25')
                                    ->exists(),
            ],
            'post_likes_100' => [
                'key'       => 'post_likes_100',
                'title'     => 'Hall of Fame',
                'desc'      => 'Get 100 likes on a single review.',
                'icon'      => 'ti-trophy',
                'color'     => '#059669',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', 1)
                                    ->selectRaw('review_id, COUNT(*) as total')
                                    ->groupBy('review_id')
                                    ->havingRaw('total >= 100')
                                    ->exists(),
            ],

            // ── Single-post dislike achievements ─────────────
            'post_dislikes_5' => [
                'key'       => 'post_dislikes_5',
                'title'     => 'Stirring the Pot',
                'desc'      => 'Get 5 dislikes on a single review.',
                'icon'      => 'ti-alert-triangle',
                'color'     => '#fb923c',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', -1)
                                    ->selectRaw('review_id, COUNT(*) as total')
                                    ->groupBy('review_id')
                                    ->havingRaw('total >= 5')
                                    ->exists(),
            ],
            'post_dislikes_25' => [
                'key'       => 'post_dislikes_25',
                'title'     => 'Public Enemy',
                'desc'      => 'Get 25 dislikes on a single review.',
                'icon'      => 'ti-shield-x',
                'color'     => '#f97316',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::whereIn('review_id', $u->reviews()->select('id'))
                                    ->where('vote', -1)
                                    ->selectRaw('review_id, COUNT(*) as total')
                                    ->groupBy('review_id')
                                    ->havingRaw('total >= 25')
                                    ->exists(),
            ],

            // ── Friend level achievements ────────────────────
            'friends_level_5' => [
                'key'       => 'friends_level_5',
                'title'     => 'Seasoned Circle',
                'desc'      => 'Have 5 friends who have each reached level 5.',
                'icon'      => 'ti-users',
                'color'     => '#60a5fa',
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()
                                    ->whereHas('userLevel', fn($q) => $q->where('level', '>=', 5))
                                    ->count() >= 5,
            ],
            'friends_level_10' => [
                'key'       => 'friends_level_10',
                'title'     => 'Connoisseur Crew',
                'desc'      => 'Have 5 friends who have each reached level 10.',
                'icon'      => 'ti-star',
                'color'     => '#3b82f6',
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()
                                    ->whereHas('userLevel', fn($q) => $q->where('level', '>=', 10))
                                    ->count() >= 5,
            ],
            'friends_level_15' => [
                'key'       => 'friends_level_15',
                'title'     => 'Elite Network',
                'desc'      => 'Have 5 friends who have each reached level 15.',
                'icon'      => 'ti-crown',
                'color'     => '#818cf8',
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()
                                    ->whereHas('userLevel', fn($q) => $q->where('level', '>=', 15))
                                    ->count() >= 5,
            ],
            'friends_level_50' => [
                'key'       => 'friends_level_50',
                'title'     => 'In The Hero\'s Party',
                'desc'      => 'Have 5 friends who have each reached level 50.',
                'icon'      => 'ti-crown',
                'color'     => '#818cf8',
                'secret'    => false,
                'condition' => fn(User $u) => $u->friends()
                                    ->whereHas('userLevel', fn($q) => $q->where('level', '>=', 50))
                                    ->count() >= 5,
            ],

            // ── Level achievements ────────────────────────
            'level_5' => [
                'key'       => 'level_5',
                'title'     => 'Getting Started',
                'desc'      => 'Reach level 5.',
                'icon'      => 'ti-seedling',
                'color'     => '#a5d6a7',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 5,
            ],
            'level_10' => [
                'key'       => 'level_10',
                'title'     => 'Game Connoisseur',
                'desc'      => 'Reach level 10.',
                'icon'      => 'ti-award',
                'color'     => '#4fc3f7',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 10,
            ],
            'level_15' => [
                'key'       => 'level_15',
                'title'     => 'Elite Reviewer',
                'desc'      => 'Reach level 15.',
                'icon'      => 'ti-star',
                'color'     => '#f59e0b',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 15,
            ],
            'level_20' => [
                'key'       => 'level_20',
                'title'     => 'Legendary Critic',
                'desc'      => 'Reach level 20.',
                'icon'      => 'ti-trophy',
                'color'     => '#fbbf24',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 20,
            ],
            'level_25' => [
                'key'       => 'level_25',
                'title'     => 'Dedicated Gamer',
                'desc'      => 'Reach level 25.',
                'icon'      => 'ti-medal',
                'color'     => '#fb923c',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 25,
            ],
            'level_30' => [
                'key'       => 'level_30',
                'title'     => 'Seasoned Veteran',
                'desc'      => 'Reach level 30.',
                'icon'      => 'ti-shield',
                'color'     => '#f97316',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 30,
            ],
            'level_35' => [
                'key'       => 'level_35',
                'title'     => 'Expert Analyst',
                'desc'      => 'Reach level 35.',
                'icon'      => 'ti-writing',
                'color'     => '#ef4444',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 35,
            ],
            'level_40' => [
                'key'       => 'level_40',
                'title'     => 'Master Critic',
                'desc'      => 'Reach level 40.',
                'icon'      => 'ti-crown',
                'color'     => '#e8192c',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 40,
            ],
            'level_45' => [
                'key'       => 'level_45',
                'title'     => 'Gaming Scholar',
                'desc'      => 'Reach level 45.',
                'icon'      => 'ti-book',
                'color'     => '#c084fc',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 45,
            ],
            'level_50' => [
                'key'       => 'level_50',
                'title'     => 'Half Century',
                'desc'      => 'Reach level 50.',
                'icon'      => 'ti-flame',
                'color'     => '#a855f7',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 50,
            ],
            'level_55' => [
                'key'       => 'level_55',
                'title'     => 'True Enthusiast',
                'desc'      => 'Reach level 55.',
                'icon'      => 'ti-rocket',
                'color'     => '#818cf8',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 55,
            ],
            'level_60' => [
                'key'       => 'level_60',
                'title'     => 'Diamond Reviewer',
                'desc'      => 'Reach level 60.',
                'icon'      => 'ti-diamond',
                'color'     => '#60a5fa',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 60,
            ],
            'level_65' => [
                'key'       => 'level_65',
                'title'     => 'Titan of Taste',
                'desc'      => 'Reach level 65.',
                'icon'      => 'ti-sword',
                'color'     => '#3b82f6',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 65,
            ],
            'level_70' => [
                'key'       => 'level_70',
                'title'     => 'Grand Master',
                'desc'      => 'Reach level 70.',
                'icon'      => 'ti-chess-queen',
                'color'     => '#2563eb',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 70,
            ],
            'level_75' => [
                'key'       => 'level_75',
                'title'     => 'Living Legend',
                'desc'      => 'Reach level 75.',
                'icon'      => 'ti-medal-2',
                'color'     => '#fbbf24',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 75,
            ],
            'level_80' => [
                'key'       => 'level_80',
                'title'     => 'Hall of Famer',
                'desc'      => 'Reach level 80.',
                'icon'      => 'ti-building',
                'color'     => '#f59e0b',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 80,
            ],
            'level_85' => [
                'key'       => 'level_85',
                'title'     => 'Mythic Reviewer',
                'desc'      => 'Reach level 85.',
                'icon'      => 'ti-ghost',
                'color'     => '#34d399',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 85,
            ],
            'level_90' => [
                'key'       => 'level_90',
                'title'     => 'Transcendent',
                'desc'      => 'Reach level 90.',
                'icon'      => 'ti-infinity',
                'color'     => '#10b981',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 90,
            ],
            'level_95' => [
                'key'       => 'level_95',
                'title'     => 'Almost There',
                'desc'      => 'Reach level 95.',
                'icon'      => 'ti-target-arrow',
                'color'     => '#e8192c',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 95,
            ],
            'level_100' => [
                'key'       => 'level_100',
                'title'     => 'Maximum Level',
                'desc'      => 'Reach level 100.',
                'icon'      => 'ti-universe',
                'color'     => '#fbbf24',
                'secret'    => false,
                'condition' => fn(User $u) => optional($u->userLevel)->level >= 100,
            ],

            // ── Votes cast achievements ──────────────────────
            'voted_dislike_1' => [
                'key'       => 'voted_dislike_1',
                'title'     => 'Not Feeling It',
                'desc'      => 'Dislike your first review.',
                'icon'      => 'ti-thumb-down',
                'color'     => '#f87171',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::where('user_id', $u->id)
                                    ->where('vote', -1)->count() >= 1,
            ],
            'voted_dislike_10' => [
                'key'       => 'voted_dislike_10',
                'title'     => 'Tough Crowd',
                'desc'      => 'Dislike 10 reviews.',
                'icon'      => 'ti-mood-sad',
                'color'     => '#ef4444',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::where('user_id', $u->id)
                                    ->where('vote', -1)->count() >= 10,
            ],
            'voted_like_1' => [
                'key'       => 'voted_like_1',
                'title'     => 'Spreading The Love',
                'desc'      => 'Like your first review.',
                'icon'      => 'ti-thumb-up',
                'color'     => '#4ade80',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::where('user_id', $u->id)
                                    ->where('vote', 1)->count() >= 1,
            ],
            'voted_like_10' => [
                'key'       => 'voted_like_10',
                'title'     => 'Generous Reviewer',
                'desc'      => 'Like 10 reviews.',
                'icon'      => 'ti-thumb-up',
                'color'     => '#22c55e',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::where('user_id', $u->id)
                                    ->where('vote', 1)->count() >= 10,
            ],
            'voted_like_50' => [
                'key'       => 'voted_like_50',
                'title'     => 'Encourager',
                'desc'      => 'Like 50 reviews.',
                'icon'      => 'ti-heart',
                'color'     => '#16a34a',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::where('user_id', $u->id)
                                    ->where('vote', 1)->count() >= 50,
            ],
            'voted_like_100' => [
                'key'       => 'voted_like_100',
                'title'     => 'Big Supporter',
                'desc'      => 'Like 100 reviews.',
                'icon'      => 'ti-heart-filled',
                'color'     => '#15803d',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::where('user_id', $u->id)
                                    ->where('vote', 1)->count() >= 100,
            ],
            'voted_like_1000' => [
                'key'       => 'voted_like_1000',
                'title'     => 'Like Machine',
                'desc'      => 'Like 1000 reviews.',
                'icon'      => 'ti-rocket',
                'color'     => '#a855f7',
                'secret'    => false,
                'condition' => fn(User $u) => ReviewVote::where('user_id', $u->id)
                                    ->where('vote', 1)->count() >= 1000,
            ],

            // ── Secret achievements ──────────────────────────
            'early_adopter' => [
                'key'       => 'early_adopter',
                'title'     => 'Early Adopter',
                'desc'      => 'One of the first 100 users to join.',
                'icon'      => 'ti-rocket',
                'color'     => '#818cf8',
                'secret'    => false,
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
            // Review-based titles
            'first_review'  => 'Basically a proffessional',
            'review_5'      => 'Junior Fact Speaker',
            'review_10'     => 'You Have Reviewed',
            'review_25'     => 'Fact Speaker',
            'review_50'     => 'Medior Fact Speaker',
            'review_100'    => 'John Review',
            // Social titles
            'first_friend'  => 'We\'re having soft taco\'s later',
            'friends_5'     => 'Social Gamer',
            'friends_10'    => 'I Don\'t Need A Weapon; My Friends Are My Power',
            'friends_151'    => 'Gotta Friend \'Em All',
            // Profile titles
            'avatar_set'    => 'Looking Cool, Fact Speaker!',
            'early_adopter'     => 'Beater',
            // Total likes
            'likes_1'           => 'Good Answer Nephew!!!',
            'likes_10'          => 'Well Received',
            'likes_50'          => 'Fan Favourite',
            'likes_100'         => 'Voice of the People',
            // Total dislikes
            'dislikes_1'        => 'You Alone On This One Lil Bro',
            'dislikes_10'       => 'Unpopular Opinion',
            'dislikes_50'       => 'Yall Can\'t Handle The Truth',
            // Single-post likes
            'post_likes_5'      => 'Hit Reviewer',
            'post_likes_25'     => 'Going Viral',
            'post_likes_100'    => 'Gem Alert!',
            // Votes cast
            'voted_dislike_1'   => 'FINISH HIM!',
            'voted_dislike_10'  => 'The Review Is A Lie',
            // Likes cast
            'voted_like_1'      => 'It\'s Dangerous To Go Alone, Take This',
            'voted_like_10'     => 'Generous Reviewer',
            'voted_like_50'     => 'Encourager',
            'voted_like_100'    => 'Big Supporter',
            'voted_like_1000'   => 'Like Machine',
            // Single-post dislikes
            'post_dislikes_5'   => 'Gotta Be Ragebait',
            'post_dislikes_25'  => 'Public Enemy',
            // Friend level
            'friends_level_5'   => 'Seasoned Circle',
            'friends_level_10'  => 'Connoisseur Crew',
            'friends_level_15'  => 'Elite Network',
            'friends_level_50'  => 'In The Hero\'s Party',
            // Level achievements
            'level_5'           => 'Getting The Hang of It',
            'level_10'          => 'Game Connoisseur',
            'level_15'          => 'Elite Reviewer',
            'level_20'          => 'Legendary Critic',
            'level_25'          => 'Dedicated Gamer',
            'level_30'          => 'Seasoned Veteran',
            'level_35'          => 'Expert Analyst',
            'level_40'          => 'Master Critic',
            'level_45'          => 'Gaming Scholar',
            'level_50'          => 'Half Century',
            'level_55'          => 'Could Prestige, Chooses Not To',
            'level_60'          => 'Diamond Reviewer',
            'level_65'          => 'Titan of Taste',
            'level_70'          => 'Grand Master',
            'level_75'          => 'Living Legend',
            'level_80'          => 'Hall of Famer',
            'level_85'          => 'Mythic Reviewer',
            'level_90'          => 'Transcendent',
            'level_95'          => 'Almost There',
            'level_100'         => 'John Fact Speaker',
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
