<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\ReviewVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewVoteController extends Controller
{
    /**
     * Toggle a like or dislike on a review.
     * - Voting the same type again → removes the vote (toggle off)
     * - Voting the opposite type  → switches the vote
     */
    public function store(Request $request, $reviewId)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'You must be logged in to vote.'], 401);
        }

        $request->validate([
            'vote' => ['required', 'in:like,dislike'],
        ]);

        $review = Review::findOrFail($reviewId);

        // Prevent voting on your own review
        if ($review->user_id === Auth::id()) {
            return response()->json(['message' => 'You cannot vote on your own review.'], 403);
        }

        $existing = ReviewVote::where('user_id', Auth::id())
                              ->where('review_id', $reviewId)
                              ->first();

        if ($existing) {
            if ($existing->vote === $request->vote) {
                // Same vote again → remove it (toggle off)
                $existing->delete();
                $userVote = null;
            } else {
                // Opposite vote → switch it
                $existing->update(['vote' => $request->vote]);
                $userVote = $request->vote;
            }
        } else {
            ReviewVote::create([
                'user_id'   => Auth::id(),
                'review_id' => $reviewId,
                'vote'      => $request->vote,
            ]);
            $userVote = $request->vote;
        }

        $likes    = ReviewVote::where('review_id', $reviewId)->where('vote', 'like')->count();
        $dislikes = ReviewVote::where('review_id', $reviewId)->where('vote', 'dislike')->count();

        return response()->json([
            'likes'     => $likes,
            'dislikes'  => $dislikes,
            'user_vote' => $userVote,
        ]);
    }
}
