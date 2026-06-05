<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Friendship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FriendshipController extends Controller
{
    public function send(Request $request, User $user)
    {
        if ($user->id === Auth::id()) {
            return response()->json(['message' => 'You cannot add yourself.'], 422);
        }

        $existing = Auth::user()->friendshipWith($user);

        if ($existing) {
            return response()->json(['message' => 'Request already exists.'], 422);
        }

        Friendship::create([
            'sender_id'   => Auth::id(),
            'receiver_id' => $user->id,
            'status'      => 'pending',
        ]);

        return response()->json(['message' => 'Friend request sent.', 'status' => 'pending']);
    }

    public function accept(Request $request, User $user)
    {
        $friendship = Friendship::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update(['status' => 'accepted']);

        app(\App\Services\AchievementService::class)->checkAndAward(Auth::user());

        return response()->json(['message' => 'Friend request accepted.', 'status' => 'accepted']);
    }

    public function decline(Request $request, User $user)
    {
        $friendship = Friendship::where('sender_id', $user->id)
            ->where('receiver_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();

        $friendship->update(['status' => 'declined']);

        return response()->json(['message' => 'Request declined.', 'status' => 'declined']);
    }

    public function remove(User $user)
    {
        Friendship::where(function ($q) use ($user) {
            $q->where('sender_id', Auth::id())->where('receiver_id', $user->id);
        })->orWhere(function ($q) use ($user) {
            $q->where('sender_id', $user->id)->where('receiver_id', Auth::id());
        })->delete();

        return response()->json(['message' => 'Friend removed.', 'status' => 'none']);
    }

    public function index()
    {
        $user    = Auth::user();
        $friends = $user->friends();

        $pending = Friendship::where('receiver_id', $user->id)
            ->where('status', 'pending')
            ->with('sender')
            ->get();

        return view('friends.index', compact('friends', 'pending'));
    }
}
