<?php

namespace App\Traits;

use App\Enums\CartStatus;
use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Exceptions\CheckoutException;
use App\Models\AppliedCoupon;
use App\Models\AssignProductAttribute;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Deposit;
use App\Models\GeneralSetting;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\ShippingMethod;
use App\Models\StockLog;
use App\Models\User;

trait OrderManager
{
    public function checkout($request, $type): \Illuminate\Database\Eloquent\Collection|bool|array
    {
        $general = GeneralSetting::query()->first();


        /* Type 1 (Order for Customer) Type 2 (Order as Gift) */

        $request->validate([
            'address' => 'required|max:50',
            'payment' => 'required|in:1,2'
        ]);

        if ($request->payment == 2) {

            $payment_status = 2;

            if (!$general->cod) {
                throw new CheckoutException('Cash on delivery is not available now');
            }
        } else {
            $payment_status = 0;
        }

        $carts_data = Cart::query()
            ->where('session_id', session('session_id'))
            ->orWhere('user_id', auth()->user()->id ?? null)
            ->where('status', CartStatus::ACCEPTED)
            ->with('product') // Simple eager loading without conditions
            ->get();

        $carts_array = $carts_data->toArray();
        $amounts = array_column($carts_array, 'offer_price');
        $total = array_sum($amounts);
        $balance = auth()->user()->wallet->balance;
        if ($total >= $balance) {
            return false;
        }


        $cart_total = 0;

        foreach ($carts_data as $cart) {
            $cart_total = $cart->offer_price * $cart->quantity;
        }


        foreach ($carts_data as $cart) {
            $order = new Order();
            $order->order_number = getTrx();
            $order->user_id = auth()->user()->id;
            $order->seller_id = $cart->product->seller_id;
            $order->order_type = $type;
            $order->payment_status = $payment_status ?? 0;
            $order->total_amount = getAmount($cart->offer_price * $cart->quantity);
            $order->save();

            $od = new OrderDetail();
            $od->order_id = $order->id;
            $od->product_id = $cart->product_id;
            $od->quantity = $cart->quantity;
            $od->base_price = $cart->offer_price;
            $od->seller_id = $cart->product->seller_id;
            $order->total_price = getAmount($cart->offer_price * $cart->quantity);
            $od->save();
        }

        session()->put('order_number', $order->order_number);

        $product = Product::where('id', $cart->product_id)->first();
        $product->track_inventory -= $cart->quantity;
        if ($product->track_inventory == 0) {
            $product->status = ProductStatus::DELIST;

        }
        $product->save();

        //Remove coupon from session
        if (session('coupon')) {
            session()->forget('coupon');
        }

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id ?? null)->get();

        foreach ($carts_data as $cart) {
            $cart->delete();
        }
        $allOrders = Order::query()->where('user_id', auth()->user()->id)->where('status', OrderStatus::PENDING)->get();

        return $allOrders;
    }
}
