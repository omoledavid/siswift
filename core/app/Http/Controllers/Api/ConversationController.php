<?php

namespace App\Http\Controllers\Api;

use App\Enums\CartStatus;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Notifications\MessageReceivedNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends Controller
{
    // List all conversations for the authenticated user
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $conversations = Conversation::query()->where('buyer_id', $user->id)
            ->orWhere('seller_id', $user->seller_id)
            ->with('messages.files', 'seller', 'buyer', 'product')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'conversations' => $conversations
        ], 200);
    }

    // Start a new conversation
    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $productId = $request->product_id;
        $buyerId = $request->user()->id;

        // Retrieve the seller_id using the product_id
        $product = Product::findOrFail($productId);
        $sellerId = $product->seller_id; // Assuming that the product has a user_id field for the seller

        $conversation = Conversation::firstOrCreate([
            'product_id' => $productId,
            'buyer_id' => $buyerId,
            'seller_id' => $sellerId,
        ]);

        return response()->json([
            'status' => true,
            'conversation' => $conversation
        ], 201);
    }


    // Show a specific conversation
    public function show($id)
    {
        $conversation = Conversation::with('messages.files', 'seller', 'buyer', 'product')->findOrFail($id);

        return response()->json([
            'status' => true,
            'conversation' => $conversation
        ], 200);
    }

    // Send a message in a conversation
    public function update(Request $request, $id)
    {
        // Validate the incoming request data
        $request->validate([
            'message' => 'required|string|max:1000',
            'files.*' => 'file|max:2048', // Optional: max file size 2MB for each file
        ]);

        $conversation = Conversation::query()->findOrFail($id);

        if (!$conversation->is_active) {
            return response()->json(['message' => 'This conversation is closed.'], 403);
        }

        $message = $conversation->messages()->create([
            'user_id' => $request->user()->id,
            'message' => $request->message,
        ]);

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $filePath = $file->store('uploads/messages', 'public');
                $message->files()->create(['file_path' => $filePath]);
            }
        }

        // Notify the other user
        $recipient = $request->user()->id == $conversation->buyer_id ? $conversation->seller : $conversation->buyer;
        notify($recipient, 'RECEIVED_MESSAGE', [
            'user_name' => $recipient->fullname,
            'buyer_name' => $request->user()->fullname,
        ]);
//        $recipient->notify(new MessageReceivedNotification($message));

        return response()->json([
            'status' => true,
            'message' => $message->load('files'),
        ], 201);
    }

    // Close a conversation
    public function destroy($id)
    {
        $conversation = Conversation::findOrFail($id);
        $conversation->is_active = false;
        $conversation->save();

        return response()->json([
            'status' => true,
            'message' => 'Conversation closed successfully.'
        ], 200);
    }

    public function message($id)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Retrieve the message by ID
        $message = Message::query()->where('id', $id)->first();

        // Ensure the message exists
        if (!$message) {
            return response()->json([
                'status' => false,
                'message' => 'Message not found.'
            ], 404);
        }

        // Decode the message content (assuming it's JSON)
        $msg = json_decode($message->message);

        // Check if the decoded message contains cart data
        if (isset($msg->cart)) {
            // Update the cart's status within the JSON message
            $msg->cart->status = CartStatus::ACCEPTED;

            // Optionally, you could also update the cart model if it exists in your database
            $cart = Cart::find($msg->cart->id);
            if ($cart) {
                $cart->status = CartStatus::ACCEPTED;
                $cart->save();
            }

            // Save the updated message content back to the message
            $message->message = json_encode($msg);
            $message->save();

            // Respond with the updated cart status and message
            return response()->json([
                'status' => true,
                'message' => 'Offer Accepted.',
                'CartStatus' => $msg->cart->status,
                'cart' => $msg->cart
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Cart data not found in the message.'
            ], 400);
        }
    }


}
