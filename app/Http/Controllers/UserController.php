<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        $users = User::query()
            ->withCount('reviews')
            ->when($query, fn($q) => $q->where('name', 'like', "%{$query}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('users.index', compact('users', 'query'));
    }

    public function show(User $user)
    {
        $reviews = \App\Models\Review::where('user_id', $user->id)
            ->latest()
            ->get();

        $avatarUrl = $user->avatar
            ? Storage::url($user->avatar)
            : null;

        $friendship = auth()->check()
            ? auth()->user()->friendshipWith($user)
            : null;

        return view('users.show', compact('user', 'reviews', 'avatarUrl', 'friendship'));
    }
}
