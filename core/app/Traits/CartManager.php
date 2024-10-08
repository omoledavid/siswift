<?php

namespace App\Traits;

use App\Enums\CartStatus;
use App\Models\AssignProductAttribute;
use App\Models\Cart;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

trait CartManager
{
    public function addProductToCart($request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'  => 'required|numeric',
            'offer_price'  => 'nullable|numeric|gt:0',
        ]);


        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $product = Product::findOrFail($request->product_id);

//        return $product->track_inventory;
        if($request->quantity > $product->track_inventory){
            return response()->json('Quantity is greater than stock');
        }

        $user_id = auth()->user()->id ?? null;

        $attributes     = AssignProductAttribute::where('product_id', $request->product_id)->distinct('product_attribute_id')->with('productAttribute')->get(['product_attribute_id']);

        if ($attributes->count() > 0) {
            $count = $attributes->count();
            $validator = Validator::make($request->all(), [
                'attributes' => "required|array|min:$count"
            ], [
                'attributes.required' => 'Product variants must be selected',
                'attributes.min' => 'All product variants must be selected'
            ]);
        }

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $selected_attr = [];

        $s_id = session()->get('session_id');

        if ($s_id == null) {
            session()->put('session_id', uniqid());
            $s_id = session()->get('session_id');
        }

        $selected_attr = $request['attributes'] ?? null;

        if ($selected_attr != null) {
            sort($selected_attr);
            $selected_attr = (json_encode($selected_attr));
        }

        if ($user_id != null) {
            $cart = Cart::where('user_id', $user_id)->where('product_id', $request->product_id)->where('attributes', $selected_attr)->first();
        } else {
            $cart = Cart::where('session_id', $s_id)->where('product_id', $request->product_id)->where('attributes', $selected_attr)->first();
        }

        //Check Stock Status
//        if ($product->track_inventory) {
//            $stock_qty = ProductStock::showAvailableStock($request->product_id, $selected_attr);
//            if ($request->quantity > $stock_qty) {
//                return response()->json(['error' => 'Quantity exceeded availability']);
//            }
//        }

        if ($cart) {
            $cart->quantity  = $request->quantity;
            if ($request->offer_price) $cart->offer_price   = $request->offer_price;
            if (isset($stock_qty) && $cart->quantity > $stock_qty) {
                return response()->json(['error' => 'Sorry, You have already added maximum amount of stock']);
            }

        } else {
            $cart = new Cart();
            $cart->user_id    = auth()->user()->id ?? null;
            $cart->session_id = $s_id;
            $cart->attributes = json_decode($selected_attr);
            $cart->product_id = $request->product_id;
            $cart->quantity   = max($request->quantity, 1);
            if ($request->offer_price) $cart->offer_price   = $request->offer_price;
        }
        $cart->status = CartStatus::ACCEPTED;
        $cart->save();
        if ($product->base_price !== $request->offer_price) {
            $cart->status = CartStatus::PENDING;
            $cart->save();

            $sender = auth()->user();
            $receiverSellerId = $product->seller_id;
            $receiver = User::where('seller_id', $receiverSellerId)->first();

            if (!$receiver) {
                return response()->json(['message' => 'Seller wasn\'t found'], 404);
            }



            // Check if an existing conversation exists between the sender and receiver for the specific product
            $existingChat = Conversation::query()->where('product_id', $product->id)
                ->where('buyer_id', $sender->id)
                ->where('seller_id', $receiver->seller_id)
                ->first();

            if (!$existingChat) {
                // Create a new conversation if one does not exist
                $conversation = Conversation::create([
                    'product_id' => $product->id,
                    'buyer_id' => $sender->id,
                    'seller_id' => $receiver->seller_id,
                    'is_active' => true,
                ]);
                $message = $conversation->messages()->create([
                    'user_id' => $sender->id,
                    'message' => json_encode([
                        'title' => 'offer',
                        'amount' => $request->offer_price,
                        'product' => $product,
                        'cart' => $cart,
                    ]),
                ]);
            } else {
                $message = $existingChat->messages()->create([
                    'user_id' => $sender->id,
                    'message' => json_encode([
                        'title' => 'offer',
                        'amount' => $request->offer_price,
                        'cart' => $cart,
                        'product' => $product,
                    ]),
                ]);
            }
//            return $existingChat;

// Notify the receiver (seller or buyer) about the new message
//            $recipient = $sender->id == $existingChat->buyer_id ? $existingChat->seller : $existingChat->buyer;
//            $recipient->notify(new MessageReceivedNotification($message));

            return [
                'cart' => $cart,
                'messages' => $message,
                'cart_status' => $cart->status,
            ];
        }else{
            $cart->status = 1;
            $cart->save();
            return $cart;
        }
    }

    public function getCartItems($request)
    {
        $user    = auth()->user();

//        if ($user_id != null) {
//            $data = Cart::where('user_id', $user_id)->with(['product', 'product.stocks', 'product.categories'])
//                ->whereHas('product', function ($q) {
//                    return $q->whereHas('categories')->whereHas('brand');
//                })
//                ->orderBy('id', 'desc')
//                ->get();
//        } else {
//            $s_id       = session()->get('session_id');
//            $data = Cart::where('session_id', $s_id)
//                ->with(['product', 'product.stocks', 'product.categories',])
//                ->whereHas('product', function ($q) {
//                    return $q->whereHas('categories')->whereHas('brand');
//                })
//                ->orderBy('id', 'desc')
//                ->get();
//        }
        return  Cart::query()->where('user_id', $user->id)->with('product')->orderBy('id', 'desc')->get();
    }

    public function deleteCartItem($id)
    {
        if (session()->has('coupon')) {
            return response()->json(['error' => 'You have applied a coupon on your cart. If you want to delete any item form your cart please remove the coupon first.']);
        }

        $cart_item = Cart::findorFail($id);
        $cart_item->delete();
    }

    public function updateCartItem ($request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'  => 'required|numeric',
            'offer_price'  => 'nullable|numeric|gt:0',
        ]);

            if ($validator->fails()) {
                return response()->json($validator->errors());
            }

        $product = Product::findOrFail($request->product_id);
        $user_id = auth()->user()->id ?? null;

        $attributes     = AssignProductAttribute::where('product_id', $request->product_id)->distinct('product_attribute_id')->with('productAttribute')->get(['product_attribute_id']);

        if ($attributes->count() > 0) {
            $count = $attributes->count();
            $validator = Validator::make($request->all(), [
                'attributes' => "required|array|min:$count"
            ], [
                'attributes.required' => 'Product variants must be selected',
                'attributes.min' => 'All product variants must be selected'
            ]);

        }

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $selected_attr = [];

        $s_id = session()->get('session_id');

        if ($s_id == null) {
            session()->put('session_id', uniqid());
            $s_id = session()->get('session_id');
        }

        $selected_attr = $request['attributes'] ?? null;

        if ($selected_attr != null) {
            sort($selected_attr);
            $selected_attr = (json_encode($selected_attr));
        }

        if ($user_id != null) {
            $cart = Cart::where('user_id', $user_id)->where('product_id', $request->product_id)->where('attributes', $selected_attr)->first();
        } else {
            $cart = Cart::where('session_id', $s_id)->where('product_id', $request->product_id)->where('attributes', $selected_attr)->first();
        }

        //Check Stock Status
//        if ($product->track_inventory) {
//            $stock_qty = ProductStock::showAvailableStock($request->product_id, $selected_attr);
//            if ($request->quantity > $stock_qty) {
//                return response()->json(['error' => 'Quantity exceeded availability']);
//            }
//        }

        if ($cart) {
            // Update the quantity, making sure it doesn't go below 1
            $cart->quantity = $request->quantity;
            if ($request->offer_price) $cart->offer_price   = $request->offer_price;
            if (isset($stock_qty) && $cart->quantity > $stock_qty) {
                return response()->json(['error' => 'Sorry, You have already added maximum amount of stock']);
            }
            if ($request->$attributes) $product->attributes = $request->attributes;

            $cart->save();
        } else {
            $cart = new Cart();
            $cart->user_id    = auth()->user()->id ?? null;
            $cart->session_id = $s_id;
            $cart->attributes = json_decode($selected_attr);
            $cart->product_id = $request->product_id;
            $cart->quantity   = max($request->quantity, 1);
            if ($request->offer_price) $cart->offer_price   = $request->offer_price;
            $cart->save();
        }

        return $cart;
    }
    private function generateHash(int $sender_id, int $receiver_id): string
    {
        $participants = [$sender_id, $receiver_id];
        sort($participants);
        return md5(json_encode($participants));
    }
}
