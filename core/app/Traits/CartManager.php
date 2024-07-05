<?php

namespace App\Traits;

use App\Models\AssignProductAttribute;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductStock;
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
        if ($product->track_inventory) {
            $stock_qty = ProductStock::showAvailableStock($request->product_id, $selected_attr);
            if ($request->quantity > $stock_qty) {
                return response()->json(['error' => 'Quantity exceeded availability']);
            }
        }

        if ($cart) {
            $request->quantity > 0 ? $cart->quantity  += $request->quantity : $cart->quantity  += max($request->quantity, ($cart->quantity * -1) + 1);
            if ($request->offer_price) $cart->offer_price   = $request->offer_price;
            if (isset($stock_qty) && $cart->quantity > $stock_qty) {
                return response()->json(['error' => 'Sorry, You have already added maximum amount of stock']);
            }

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
        if ($product->track_inventory) {
            $stock_qty = ProductStock::showAvailableStock($request->product_id, $selected_attr);
            if ($request->quantity > $stock_qty) {
                return response()->json(['error' => 'Quantity exceeded availability']);
            }
        }

        if ($cart) {
            $request->quantity > 0 ? $cart->quantity  += $request->quantity : $cart->quantity  += max($request->quantity, ($cart->quantity * -1) + 1);
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
}
