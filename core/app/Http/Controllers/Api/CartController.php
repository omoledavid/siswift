<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Traits\CartManager;
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
    public function offer(Request $request, $type){
        $cart = Cart::where('id', $request->cat_id)->firstOrFail();
        if($type === 'accept'){
            if($cart->status === 1){
                return response()->json([
                    'You already Accepted this offer'
                ]);
            }
            if($cart->status === 2){
                return response()->json([
                    'You already Rejected this offer'
                ]);
            }
            $cart->status = 1;
            $cart->save();
        }elseif($type === 'reject'){
            if($cart->status === 2){
                return response()->json([
                    'You already Rejected this offer'
                ]);
            }
            if($cart->status === 1){
                return response()->json([
                    'You already Accepted this offer'
                ]);
            }
            $cart->status = 2;
            $cart->save();
        }
        return response()->json([
            $cart
        ]);
    }
}
