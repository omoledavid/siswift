<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reply;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReplyController extends Controller
{
    // Store a new reply
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'review_id' => 'required|exists:reviews,id',
            'content' => 'required|string',
        ]);

        $reply = Reply::query()->create([
            'review_id' => $validated['review_id'],
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return response()->json($reply, 201);
    }

    // Update an existing reply
    public function update(Request $request, $id): JsonResponse
    {
        $reply = Reply::query()->where('user_id', Auth::id())->find($id);

        if (!$reply) {
            return response()->json([
                'status' => false,
                'message' => 'Reply not found',
            ]);
        }

        $validated = $request->validate([
            'content' => 'required|string',
        ]);

        $reply->update([
            'content' => $validated['content'],
        ]);

        return response()->json([
            'status' => true,
            'reply' => $reply,
        ]);
    }

    // Delete a reply
    public function destroy($id): JsonResponse
    {
        $reply = Reply::query()->where('user_id', Auth::id())->find($id);

        if (!$reply) {
            return response()->json([]);
        }

        $reply->delete();

        return response()->json([
            'status' => true,
            'message' => 'Reply deleted successfully'
        ]);
    }
}
