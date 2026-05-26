<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLevel extends Model
{
    protected $fillable = [
        'user_id',
        'level',
        'xp',
        'total_xp',
        'review_count',
    ];

    protected $casts = [
        'level'        => 'integer',
        'xp'           => 'integer',
        'total_xp'     => 'integer',
        'review_count' => 'integer',
    ];

    // ── Relationship ─────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ── Computed helpers ─────────────────────────────────────────────────────

    /**
     * XP required to complete the current level.
     * Formula: level * 100   (level 1 = 100, level 2 = 200, …)
     */
    public function xpForCurrentLevel(): int
    {
        return $this->level * 100;
    }

    /** Percentage through the current level (0–100). */
    public function progressPercent(): int
    {
        return (int) round(($this->xp / $this->xpForCurrentLevel()) * 100);
    }

    /** Human-readable rank title based on level. */
    public function rankTitle(): string
    {
        return match (true) {
            $this->level === 1          => 'Fresh Gamer',
            $this->level <= 3           => 'Casual Player',
            $this->level <= 6           => 'Seasoned Critic',
            $this->level <= 10          => 'Game Connoisseur',
            $this->level <= 15          => 'Elite Reviewer',
            default                     => 'Legendary Critic',
        };
    }

    /** XP still needed to reach the next level. */
    public function xpToNextLevel(): int
    {
        return $this->xpForCurrentLevel() - $this->xp;
    }

    /** How many reviews are needed to reach the next level (at 20 XP each). */
    public function reviewsToNextLevel(): int
    {
        return (int) ceil($this->xpToNextLevel() / 20);
    }
}
