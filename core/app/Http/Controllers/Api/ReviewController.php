<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\UserReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    // Get all reviews for a specific user
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $reviews = Review::where('reviewed_user_id', $user->id)
            ->with('user', 'replies.user')
            ->get();

        if ($reviews->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No reviews yet'
            ],404);
        }

        return response()->json([
            'success' => true,
            'reviews' => $reviews
        ]);
    }

    // Store a new review
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'reviewed_user_id' => 'required|exists:users,id',
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $review = Review::create([
            'user_id' => Auth::id(),
            'reviewed_user_id' => $validated['reviewed_user_id'],
            'content' => $validated['content'],
            'rating' => $validated['rating'],
        ]);

        return response()->json([
            'status' => true,
            'review' => $review,
        ], 201);
    }

    // Update an existing review
    public function update(Request $request, $id): JsonResponse
    {
        $review = Review::query()->where('user_id', Auth::id())->find($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ]);
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $review->update([
            'content' => $validated['content'],
            'rating' => $validated['rating'],
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Review updated successfully',
            'review' => $review,
        ]);
    }

    // Delete a review
    public function destroy($id): JsonResponse
    {
        $review = Review::query()->where('user_id', Auth::id())->find($id);

        if (!$review) {
            return response()->json([
                'status' => false,
                'message' => 'Review not found'
            ], 404);
        }

        $review->delete();

        return response()->json([
            'status' => true,
            'message' => 'Review deleted successfully'
        ]);
    }
}
