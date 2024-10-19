<?php

namespace App\Http\Controllers\Api;

use App\Enums\DisputeStatus;
use App\Http\Controllers\Controller;
use App\Models\Dispute;
use App\Models\DisputeReply;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisputeController extends Controller
{
    public function createDispute(Request $request, $orderId)
{
    $request->validate([
        'reason' => 'required|string|max:255',
        'image' => 'nullable|image|max:2048',
        'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:10000',
    ]);

    $order = Order::findOrFail($orderId);
    $refund = Refund::where('order_id', $order->id)->first(); // Assuming only one refund per order

    // Ensure the user is either the buyer or seller
    if (auth()->id() !== $order->user_id && auth()->id() !== $order->seller_id) {
        return response()->json(['error' => 'Unauthorized action'], 403);
    }

    // Save image and video if provided
    $imagePath = $request->file('image') ? $request->file('image')->store('dispute/images', 'public') : null;
    $videoPath = $request->file('video') ? $request->file('video')->store('dispute/videos', 'public') : null;

    // Get full URLs for the image and video
    $fullImagePath = $imagePath ? url(Storage::url($imagePath)) : null; // Create the full URL
    $fullVideoPath = $videoPath ? url(Storage::url($videoPath)) : null; // Create the full URL

    $dispute = Dispute::create([
        'order_id' => $order->id,
        'refund_id' => $refund->id, // Link dispute to the refund
        'reason' => $request->reason,
        'status' => DisputeStatus::OPEN,
        'image' => $fullImagePath,
        'video' => $fullVideoPath,
    ]);

    return response()->json($dispute, 201);
}


    public function replyToDispute(Request $request, $disputeId)
    {
        $request->validate([
            'message' => 'required|string',
            'image' => 'nullable|image|max:2048',
            'video' => 'nullable|mimes:mp4,mov,avi,wmv|max:10000',
        ]);

        $dispute = Dispute::findOrFail($disputeId);

        // Ensure the user is either the buyer or seller
        if (auth()->id() !== $dispute->order->user_id && auth()->id() !== $dispute->order->seller_id) {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        // Save image and video if provided
        $imagePath = $request->file('image') ? $request->file('image')->store('dispute/images', 'public') : null;
        $videoPath = $request->file('video') ? $request->file('video')->store('dispute/videos', 'public') : null;

        $fullImagePath = $imagePath ? url(Storage::url($imagePath)) : null; // Create the full URL
        $fullVideoPath = $videoPath ? url(Storage::url($videoPath)) : null; // Create the full URL

        $reply = DisputeReply::create([
            'dispute_id' => $dispute->id,
            'user_id' => auth()->id(),
            'message' => $request->message,
            'image' => $fullImagePath,
            'video' => $fullVideoPath,
        ]);

        return response()->json($reply, 201);
    }
}
