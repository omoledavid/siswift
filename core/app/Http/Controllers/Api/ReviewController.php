<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    public function index(){
        $user = auth()->user();
        $review = UserReview::where('seller_id', $user->seller_id)->paginate(10);
        return response()->json([
            'reviews' => $review,
        ]);
    }
    public function store(Request $request)
    {
        $user = auth()->user();

        // Validate the request inputs
        $validator = Validator::make($request->all(), [
            'seller_id' => 'required|exists:users,seller_id|exists:users,id', // Ensure the seller exists
            'review' => 'required|string',
            'rating' => 'required|min:1|max:5', // Assuming rating is an integer between 1 and 5
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validator->errors()
            ], 400);
        }

        // Ensure the user is not reviewing themselves
        if ($request->seller_id == $user->seller_id) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You can\'t rate yourself!'
            ], 403);
        }

        // Check if the user has already reviewed this seller
        $reviewExist = UserReview::where('user_id', $user->id)
            ->where('seller_id', $request->seller_id)
            ->first();

        if ($reviewExist) {
            return response()->json([
                'status' => 'failed',
                'message' => 'You already rated this seller!'
            ], 409);
        }

        // Create a new review
        $review = new UserReview();
        $review->user_id = $user->id;
        $review->seller_id = $request->seller_id;
        $review->review = $request->review;
        $review->rating = $request->rating;
        $review->save();

        // Return success response with the newly created review
        return response()->json([
            'status' => 'success',
            'review' => $review,
        ], 201);
    }

}
