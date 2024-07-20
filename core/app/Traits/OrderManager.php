<?php

namespace App\Traits;

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
    public function checkout($request, $type)
    {
        $general = GeneralSetting::first();

        /* Type 1 (Order for Customer) Type 2 (Order as Gift) */

        $request->validate([
            'address'           => 'required|max:50',
            'payment'           => 'required|in:1,2'
        ]);

        if ($request->payment  == 2) {

            $payment_status = 2;

            if (!$general->cod) {
                throw new CheckoutException('Cash on delivery is not available now');
            }
        }else{
            $payment_status = 1;
        }

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id ?? null)->with(['product' => function ($q) {
            return $q->whereHas('categories')->whereHas('brand');
        }, 'product.categories'])->get();
        $carts_array = $carts_data->toArray();
        $amounts = array_column($carts_array, 'offer_price');
        $total = array_sum($amounts);
        $balance = auth()->user()->wallet->balance;
        if($total > $balance){
            return false;
        }



        $cart_total     = 0;

        foreach ($carts_data as $cart) {
           $cart_total = $cart->offer_price * $cart->quantity;
        }

        $order = new Order();
        $order->order_number        = getTrx();
        $order->user_id             = auth()->user()->id;
        $order->order_type          = $type;
        $order->payment_status      = $payment_status ?? 0;
        $order->save();

        foreach ($carts_data as $cart) {
            $od = new OrderDetail();
            $od->order_id       = $order->id;
            $od->product_id     = $cart->product_id;
            $od->quantity       = $cart->quantity;
            $od->base_price     = $cart->product->base_price;
            $od->seller_id      = $cart->product->seller_id;
            $od->save();
        }

        $order->total_amount =  getAmount($cart_total);
        $order->save();
        session()->put('order_number', $order->order_number);

        //Remove coupon from session
        if (session('coupon')) {
            session()->forget('coupon');
        }

        if ($request->payment != 1) {
            $depo['user_id']            = auth()->id();
            $depo['method_code']        = 0;
            $depo['order_id']           = $order->id;
            $depo['method_currency']    = $general->cur_text;
            $depo['amount']             = $order->total_amount;
            $depo['charge']             = 0;
            $depo['rate']               = 0;
            $depo['final_amo']          = getAmount($order->total_amount);
            $depo['btc_amo']            = 0;
            $depo['btc_wallet']         = "";
            $depo['trx']                = getTrx();
            $depo['try']                = 0;
            $depo['status']             = 2;
            $deposit                    = Deposit::where('order_id', $order->id)->first();

            if ($deposit) {
                $deposit->update($depo);
                $data = $deposit;
            } else {
                $data = Deposit::create($depo);
            }

            $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id ?? null)->get();

            foreach ($carts_data as $cart) {
                $cart->delete();
            }
        }

        return $order;
    }
}
