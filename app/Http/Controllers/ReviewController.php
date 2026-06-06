<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Services\AchievementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $gameId)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'You must be logged in to leave a review.'], 401);
        }

        $existing = Review::where('user_id', Auth::id())
                          ->where('game_id', $gameId)
                          ->first();

        if ($existing) {
            return response()->json(['message' => 'You have already reviewed this game.'], 422);
        }

        $data = $request->validate([
            'rating'      => ['required', 'numeric', 'min:1', 'max:10'],
            'body'        => ['nullable', 'string', 'min:10', 'max:2000'],
            'is_detailed' => ['boolean'],
            'categories'  => ['nullable', 'array'],
            'categories.*.name'   => ['required_with:categories', 'string', 'max:40'],
            'categories.*.rating' => ['required_with:categories', 'integer', 'min:1', 'max:10'],
        ]);

        $review = Review::create([
            'user_id'     => Auth::id(),
            'game_id'     => $gameId,
            'game_name'   => $request->input('game_name'),
            'rating'      => $data['rating'],
            'body'        => $data['body'] ?? null,
            'is_detailed' => $data['is_detailed'] ?? false,
            'categories'  => $data['categories'] ?? null,
        ]);

        $review->load('user');

        // check achievements
        $newAchievements = app(AchievementService::class)->checkAndAward(Auth::user());

        return response()->json([
            'message'          => 'Review posted!',
            'new_achievements' => $newAchievements,
            'review'           => [
                'id'          => $review->id,
                'rating'      => $review->rating,
                'body'        => $review->body,
                'is_detailed' => $review->is_detailed,
                'categories'  => $review->categories,
                'created_at'  => $review->created_at->format('M j, Y'),
                'user'        => ['name' => $review->user->name],
            ],
        ], 201);
    }

    public function destroy($gameId, $reviewId)
    {
        $review = Review::where('id', $reviewId)
                        ->where('game_id', $gameId)
                        ->where('user_id', Auth::id())
                        ->firstOrFail();

        $review->delete();

        return response()->json(['message' => 'Review deleted.']);
    }
}
