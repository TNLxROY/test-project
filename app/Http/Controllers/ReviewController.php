<?php

namespace App\Http\Controllers;

use App\Models\Review;
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
            'body' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $review = Review::create([
            'user_id'   => Auth::id(),
            'game_id'   => $gameId,
            'game_name' => $request->input('game_name'),
            'body'      => $data['body'],
        ]);

        $review->load('user');

        return response()->json([
            'message' => 'Review posted!',
            'review'  => [
                'id'         => $review->id,
                'body'       => $review->body,
                'created_at' => $review->created_at->format('M j, Y'),
                'user'       => ['name' => $review->user->name],
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
