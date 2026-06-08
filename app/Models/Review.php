<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'user_id',
        'game_id',
        'game_name',
        'body',
        'rating',
        'categories',
        'is_detailed',
    ];

    protected $casts = [
        'rating'      => 'decimal:2',
        'categories'  => 'array',
        'is_detailed' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function votes()
    {
        return $this->hasMany(\App\Models\ReviewVote::class);
    }
}
