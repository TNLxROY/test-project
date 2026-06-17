<?php
/* ════════════════════════════════════════════════════════════════
 * app/Models/Ruleset.php
 * ════════════════════════════════════════════════════════════════ */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ruleset extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'rawg_id',
        'game_name',
        'game_image',
        'title',
        'description',
        'rules',      // stored as JSON array
        'mod_url',
        'is_public',
    ];

    protected $casts = [
        'rules'     => 'array',
        'is_public' => 'boolean',
        'rawg_id'   => 'integer',
    ];

    /* ── Relationships ─────────────────────────────────────────── */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /* ── Scopes ────────────────────────────────────────────────── */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
