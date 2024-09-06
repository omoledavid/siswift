<?php

namespace App\Http\Controllers\Api;

use App\Enums\CartStatus;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Message;
use App\Traits\CartManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use CartManager;

    public function index(Request $request)
    {
        $user = auth()->user();
        $cart = $this->getCartItems($request);
        return response()->json([
            'status' => 'success',
            'data' => $cart
        ]);
    }

    public function store(Request $request)
    {
        $cart = $this->addProductToCart($request);

        return response()->json([
            'status' => 'success',
            'data' => $cart
        ]);
    }
    public function show(Cart $cart){
        return response()->json([
            'status' => 'success',
            'data' => $cart
        ]);
    }

    public function update(Request $request)
    {
        $cart = $this->updateCartItem($request);

        return response()->json([
            'status' => 'success',
            'data' => $cart
        ]);
    }

    public function destroy(Cart $cart)
    {
        $this->deleteCartItem($cart->id);
        return response()->json([
            'status' => 'success',
            'message' => 'cart item deleted successfully'
        ]);
    }
    public function offer(Request $request, $type, $id): JsonResponse
    {
        $cart = Cart::query()->where('id', $request->cat_id)->firstOrFail();
        if($type === 'accept'){
            if($cart->status === CartStatus::ACCEPTED){
                return response()->json([
                    'You already Accepted this offer'
                ]);
            }
            if($cart->status === CartStatus::REJECTED){
                return response()->json([
                    'You already Rejected this offer'
                ]);
            }
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
        }elseif($type === 'reject'){
            if($cart->status === CartStatus::REJECTED){
                return response()->json([
                    'You already Rejected this offer'
                ]);
            }
            if($cart->status === CartStatus::ACCEPTED){
                return response()->json([
                    'You already Accepted this offer'
                ]);
            }
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
                $msg->cart->status = CartStatus::REJECTED;

                // Optionally, you could also update the cart model if it exists in your database
                $cart = Cart::find($msg->cart->id);
                if ($cart) {
                    $cart->status = CartStatus::REJECTED;
                    $cart->save();
                }

                // Save the updated message content back to the message
                $message->message = json_encode($msg);
                $message->save();

                // Respond with the updated cart status and message
                return response()->json([
                    'status' => true,
                    'message' => 'Offer Rejected.',
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
        return response()->json([
            $cart
        ]);
    }
}
