<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Friendship;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function reviews()
    {
        return $this->hasMany(\App\Models\Review::class);
    }

    public function sentFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    public function friendshipWith(User $user): ?Friendship
    {
        return Friendship::where(function ($q) use ($user) {
        $q->where('sender_id', $this->id)->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user) {
        $q->where('sender_id', $user->id)->where('receiver_id', $this->id);
        })->first();
    }

    public function friends()
    {
        $sentIds     = Friendship::where('sender_id', $this->id)->where('status', 'accepted')->pluck('receiver_id');
        $receivedIds = Friendship::where('receiver_id', $this->id)->where('status', 'accepted')->pluck('sender_id');

        return User::whereIn('id', $sentIds->merge($receivedIds))->get();
    }
}
