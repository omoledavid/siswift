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
        }

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id ?? null)->with(['product' => function ($q) {
            return $q->whereHas('categories')->whereHas('brand');
        }, 'product.categories'])->get();



        $cart_total     = 0;
        $product_categories = [];

        foreach ($carts_data as $cart) {
            $product_categories[] = $cart->product->categories->pluck('id')->toArray();


//            if ($cart->attributes != null) {
//                $attr_item                   = AssignProductAttribute::productAttributesDetails($cart->attributes);
//                $attr_item['offer_amount'] = $offer_amount;
//                $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
//                unset($attr_item['extra_price']);
//            } else {
//                $details['variants']        = null;
//                $details['offer_amount']    = $offer_amount;
//                $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
//            }
            $cart_total = $cart->offer_price * $cart->quantity;
        }

        foreach ($carts_data as $cd) {
            $pid    = $cd->product_id;
            $attr   = $cd->attributes;
            $attr   = $cd->attributes ? json_encode($cd->attributes) : null;
            if ($cd->product->track_inventory) {
                $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();
                if ($stock) {
                    $stock->quantity   -= $cd->quantity;
                    $stock->save();
                    $log = new StockLog();
                    $log->stock_id  = $stock->id;
                    $log->quantity  = $cd->quantity;
                    $log->type      = 2;
                    $log->save();
                }
            }
        }

        $shipping_data      = ShippingMethod::find($request->shipping_method);

        $shipping_address   = [
            'address'   => $request->address,
        ];

        $order = new Order();
        $order->order_number        = getTrx();
        $order->user_id             = auth()->user()->id;
        $order->order_type          = $type;
        $order->payment_status      = $payment_status ?? 0;
        $order->save();
        $details = [];
//        return response()->json([$cart->product->seller_id]);

        foreach ($carts_data as $cart) {
            $od = new OrderDetail();
            $od->order_id       = $order->id;
            $od->product_id     = $cart->product_id;
            $od->quantity       = $cart->quantity;
            $od->base_price     = $cart->product->base_price;
            $od->seller_id      = $cart->product->seller_id;

            if ($cart->product->offer && $cart->product->offer->activeOffer) {
                $offer_amount       = calculateDiscount($cart->product->offer->activeOffer->amount, $cart->product->offer->activeOffer->discount_type, $cart->product->base_price);
            } else $offer_amount = 0;


//            if ($cart->attributes != null) {
//                $attr_item                   = AssignProductAttribute::productAttributesDetails($cart->attributes);
//                $attr_item['offer_amount']   = $offer_amount;
//                $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
//                $od->total_price             = $sub_total;
////                unset($attr_item['extra_price']);
//                $od->details                 = json_encode($attr_item);
//            } else {
//                $details['variants']        = null;
//                $details['offer_amount']    = $offer_amount;
//                $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
//                $od->total_price            = $sub_total;
//                $od->details                = json_encode($details);
//            }
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
