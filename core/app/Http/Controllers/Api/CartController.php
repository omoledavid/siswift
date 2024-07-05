<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Traits\CartManager;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use CartManager;

    public function store(Request $request)
    {
        $cart = $this->addProductToCart($request);

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
        return response()->noContent();
    }
}
