<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\AppliedCoupon;
use App\Models\Coupon;
use App\Models\Deposit;
use App\Models\StockLog;
use App\Models\OrderDetail;
use App\Models\ProductStock;
use Illuminate\Http\Request;
use App\Models\GeneralSetting;
use App\Models\ShippingMethod;
use App\Models\AssignProductAttribute;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function __construct() {
        $this->activeTemplate = activeTemplate();
    }

    public function orders($type)
    {
        $pageTitle = ucfirst($type).' Orders';
        $emptyMessage = 'No order yet';
        $query = Order::where('user_id', auth()->user()->id)->whereIn('payment_status', [1,2]);
        if($type == 'pending'){
            $query = $query->where('status', 0);
        }elseif($type == 'processing'){
            $query = $query->where('status', 1);
        }elseif($type == 'dispatched'){
            $query = $query->where('status', 2);
        }elseif($type == 'completed'){
            $query = $query->where('status', 3);
        }elseif($type == 'canceled'){
            $query = $query->where('status', 4);
        }

        $orders = $query->latest()->paginate(getPaginate());
        return view($this->activeTemplate. 'user.orders.index', compact('pageTitle', 'orders', 'emptyMessage', 'type'));
    }

    public function orderDetails($order_number)
    {
        $pageTitle = 'Order Details';
        $order = Order::where('order_number', $order_number)->where('user_id', auth()->user()->id)->with('deposit', 'orderDetail','appliedCoupon')->first();

        return view($this->activeTemplate. 'user.orders.details', compact('order','pageTitle'));
    }

    public function confirmOrder(Request $request, $type)
    {
        $general = GeneralSetting::first();

        /* Type 1 (Order for Customer) Type 2 (Order as Gift) */

        $request->validate([
            'shipping_method'   => 'required|integer',
            'firstname'         => 'required|max:50',
            'lastname'          => 'required|max:50',
            'mobile'            => 'required|max:50',
            'email'             => 'required|max:90',
            'address'           => 'required|max:50',
            'city'              => 'required|max:50',
            'state'             => 'required|max:50',
            'zip'               => 'required|max:50',
            'country'           => 'required|max:50',
            'payment'           => 'required|in:1,2'
        ]);

        if($request->payment  == 2){

            $payment_status = 2;

            if(!$general->cod){
                $notify[]=['error','Cash on delivery is not available now'];
                return back()->withNotify($notify);
            }
        }

        $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id??null)->with(['product' => function($q){
            return $q->whereHas('categories')->whereHas('brand');
        }, 'product.categories'])->get();


        $coupon_amount  = 0;
        $coupon_code    = null;
        $cart_total     = 0;
        $product_categories = [];

        foreach ($carts_data as $cart) {
            $product_categories[] = $cart->product->categories->pluck('id')->toArray();

            if($cart->product->offer && $cart->product->offer->activeOffer){
                $offer_amount       = calculateDiscount($cart->product->offer->activeOffer->amount, $cart->product->offer->activeOffer->discount_type, $cart->product->base_price);
            }else{
                $offer_amount       = 0;
            }

            if($cart->attributes != null){
                $attr_item                   = AssignProductAttribute::productAttributesDetails($cart->attributes);
                $attr_item['offer_amount'] = $offer_amount;
                $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                unset($attr_item['extra_price']);
            }else{
                $details['variants']        = null;
                $details['offer_amount']    = $offer_amount;
                $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
            }
            $cart_total += $sub_total;
        }

        if(session('coupon')){
            $coupon = Coupon::where('coupon_code', session('coupon')['code'])->with('categories')->first();
            // Check Minimum Subtotal
            if($cart_total < $coupon->minimum_spend){
                return response()->json(['error' => "Sorry your have to order minmum amount of $coupon->minimum_spend $general->cur_text"]);
            }
            // Check Maximum Subtotal
            if($coupon->maximum_spend !=null && $cart_total > $coupon->maximum_spend){
                return response()->json(['error' => "Sorry your have to order maximum amount of $coupon->maximum_spend $general->cur_text"]);
            }
            //Check Limit Per Coupon
            if($coupon->appliedCoupons->count() >= $coupon->usage_limit_per_coupon){
                return response()->json(['error' => "Sorry your Coupon has exceeded the maximum Limit For Usage"]);
            }
            //Check Limit Per User
            if($coupon->appliedCoupons->where('user_id', auth()->id())->count() >= $coupon->usage_limit_per_user){
                return response()->json(['error' => "Sorry you have already reached the maximum usage limit for this coupon"]);
            }
            $product_categories = array_unique(array_flatten($product_categories));
            if($coupon){
                $coupon_categories = $coupon->categories->pluck('id')->toArray();
                $coupon_products = $coupon->products->pluck('id')->toArray();

                $cart_products = $carts_data->pluck('product_id')->unique()->toArray();

                if(empty(array_intersect($coupon_products, $cart_products))){
                    if(empty(array_intersect($product_categories, $coupon_categories))){
                        $notify[]=['error', 'The coupon is not available for some products on your cart.'];
                        return redirect()->back()->withNotify($notify);
                    }
                }
                if($coupon->discount_type == 1){
                    $coupon_amount = $coupon->coupon_amount;
                }else{
                    $coupon_amount = ($cart_total * $coupon->coupon_amount) / 100;
                }
                $coupon_code    = $coupon->coupon_code;
            }
        }

        foreach ($carts_data as $cd) {
            $pid    = $cd->product_id;
            $attr   = $cd->attributes;
            $attr   = $cd->attributes ? json_encode($cd->attributes):null;
            if($cd->product->track_inventory){
                $stock  = ProductStock::where('product_id', $pid)->where('attributes', $attr)->first();
                if($stock){
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
            'firstname' => $request->firstname,
            'lastname'  => $request->lastname,
            'mobile'    => $request->mobile,
            'country'   => $request->country,
            'city'      => $request->city,
            'state'     => $request->state,
            'zip'       => $request->zip,
            'address'   => $request->address,
        ];

        $order = new Order;
        $order->order_number        = getTrx();
        $order->user_id             = auth()->user()->id;
        $order->shipping_address    = json_encode($shipping_address);
        $order->shipping_method_id  = $request->shipping_method;
        $order->shipping_charge     = $shipping_data->charge;
        $order->order_type          = $type;
        $order->payment_status      = $payment_status??0;
        $order->save();
        $details = [];

        foreach($carts_data as $cart){
            $od = new OrderDetail();
            $od->order_id       = $order->id;
            $od->product_id     = $cart->product_id;
            $od->quantity       = $cart->quantity;
            $od->base_price     = $cart->product->base_price;
            $od->seller_id      = $cart->product->seller_id;

            if($cart->product->offer && $cart->product->offer->activeOffer){
                $offer_amount       = calculateDiscount($cart->product->offer->activeOffer->amount, $cart->product->offer->activeOffer->discount_type, $cart->product->base_price);
            }else $offer_amount = 0;


            if($cart->attributes != null){
                $attr_item                   = AssignProductAttribute::productAttributesDetails($cart->attributes);
                $attr_item['offer_amount']   = $offer_amount;
                $sub_total                   = (($cart->product->base_price + $attr_item['extra_price']) - $offer_amount) * $cart->quantity;
                $od->total_price             = $sub_total;
                unset($attr_item['extra_price']);
                $od->details                 = json_encode($attr_item);
            }else{
                $details['variants']        = null;
                $details['offer_amount']    = $offer_amount;
                $sub_total                  = ($cart->product->base_price  - $offer_amount) * $cart->quantity;
                $od->total_price            = $sub_total;
                $od->details                = json_encode($details);
            }
            $od->save();
        }

        $order->total_amount =  getAmount($cart_total - $coupon_amount + $shipping_data->charge);
        $order->save();
        session()->put('order_number', $order->order_number);

        if($coupon_code != null){
            $applied_coupon = new AppliedCoupon();
            $applied_coupon->user_id    = auth()->id();
            $applied_coupon->coupon_id  = $coupon->id;
            $applied_coupon->order_id   = $order->id;
            $applied_coupon->amount     = $coupon_amount;
            $applied_coupon->save();
        }

        //Remove coupon from session
        if(session('coupon')){
            session()->forget('coupon');
        }

        if($request->payment == 1){
            return redirect()->route('user.deposit');
        }else{

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

            if($deposit){
                $deposit->update($depo);
                $data = $deposit;
            }else{
                $data = Deposit::create($depo);
            }

            $carts_data = Cart::where('session_id', session('session_id'))->orWhere('user_id', auth()->user()->id??null)->get();

            foreach($carts_data as $cart){
                $cart->delete();
            }

            $notify[] = ['success', 'Your order has submitted successfully please wait for a confirmation email'];
            return redirect()->route('user.home')->withNotify($notify);
        }
    }

    public function trackOrder()
    {
        $pageTitle = 'Order Tracking';

        return view($this->activeTemplate. 'order_track', compact('pageTitle'));
    }

    public function getOrderTrackData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|max:160',
        ]);

        if($validator->fails()) {
            return response()->json($validator->errors());
        }

        $order_number   = $request->order_number;
        $order_data     = Order::where('order_number', $order_number)->first();
        if($order_data){
            $p_status   = $order_data->payment_status;
            $status     = $order_data->status;

            return response()->json(['success'=>true, 'payment_status' => $p_status, 'status' => $status]);
        }
        else{
            $notify = 'No order found';
            return response()->json(['success'=>false,'message'=>$notify]);
        }
    }

    public function printInvoice(Order $order)
    {
        $pageTitle = 'Print Invoice';

        return view('invoice.print', compact('pageTitle', 'order'));
    }

}
