<?php

namespace App\Http\Controllers\Admin;

use App\Models\GeneralSetting;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Models\SellLog;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function ordered()
    {
        $pageTitle     = "All Orders";
        $emptyMessage  = 'No order found';
        $query         =  Order::where('payment_status', '!=' ,0);

        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }

        $orders = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('pageTitle', 'orders', 'emptyMessage'));
    }

    public function codOrders()
    {
        $emptyMessage  = 'NO COD order found';
        $pageTitle     = "Cash On Delivery Orders";
        $query         = Order::where('payment_status',2);

        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }

        $orders        = $query->with(['user', 'deposit'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('pageTitle', 'orders', 'emptyMessage'));
    }

    public function pending()
    {
        $emptyMessage  = 'No pending order found';
        $pageTitle     = "Pending Orders";
        $query         = Order::where('payment_status', '!=' , 0)->where('status', 0);

        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders        = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('pageTitle', 'orders', 'emptyMessage'));

    }

    public function onProcessing()
    {
        $emptyMessage  = 'No Data Found';
        $pageTitle     = "Orders on Processing";
        $query         = Order::where('payment_status', '!=' ,0)->where('status', 1);

        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders        = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('pageTitle', 'orders', 'emptyMessage'));
    }

    public function dispatched()
    {
        $emptyMessage  = 'No Data Found';
        $pageTitle     = "Orders Dispatched";
        $query         = Order::where('payment_status', '!=' ,0)->where('status', 2);

        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders        = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('pageTitle', 'orders', 'emptyMessage'));
    }

    public function canceledOrders()
    {
        $emptyMessage  = 'No Data Found';
        $pageTitle     = "Cancelled Orders";

        $query         = Order::where('payment_status', '!=' ,0)->where('status', 4);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders        = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('pageTitle', 'orders', 'emptyMessage'));
    }

    public function deliveredOrders()
    {
        $emptyMessage  = 'No Data Found';
        $pageTitle     = "Delivered Orders";

        $query         = Order::where('payment_status', '!=' ,0)->where('status', 3);
        if(isset(request()->search)){
            $query->where('order_number', request()->search);
        }
        $orders        = $query->with(['user', 'deposit', 'deposit.gateway'])->orderBy('id', 'DESC')->paginate(getPaginate());
        return view('admin.order.ordered', compact('pageTitle', 'orders', 'emptyMessage'));
    }

    public function changeStatus(Request $request)
    {
        $general    = GeneralSetting::first();
        $order      = Order::findOrFail($request->id);

        if($order->status == 3){
            $notify[] = ['error', 'This order has already been delivered'];
            return back()->withNotify($notify);
        }


        $order->status = $request->action;

        if($request->action==1){
            $action = 'Processing';
        }elseif($request->action == 2){
            $action = 'Dispatched';
        }elseif($request->action == 3){
            $action = 'Delivered';
            $order->deposit->status = 1;
            $order->deposit->save();

            foreach($order->orderDetail as $detail) {
                $commission  = ($detail->total_price * $general->product_commission)/100;
                $finalAmount = $detail->total_price - $commission;

                $detail->product->sold += $detail->quantity;
                $detail->product->save();

                if($detail->seller_id != 0){
                    $seller = Seller::findOrFail($detail->seller_id);
                    $seller->balance += $finalAmount;
                    $seller->save();
                }

                $sellLog = new SellLog();
                $sellLog->seller_id       = $detail->seller_id;
                $sellLog->product_id      = $detail->product_id;
                $sellLog->order_id        = $order->order_number;
                $sellLog->qty             = $detail->quantity;
                $sellLog->product_price   = $detail->total_price;
                $sellLog->after_commission= $detail->seller_id == 0 ? 0 : $finalAmount;
                $sellLog->save();
            }

        }elseif($request->action == 4){
            $action = 'Cancelled';
        }elseif($request->action == 0){
            $action = 'Pending';
        }

        $notify[] = ['success', 'Order status changed to '.$action];
        $order->save();

        $short_code = [
            'site_name' => $general->sitename,
            'order_id'  => $order->order_number
        ];

        if($request->action == 1){
            $act = 'ORDER_ON_PROCESSING_CONFIRMATION';
        }elseif($request->action == 2){
            $act = 'ORDER_DISPATCHED_CONFIRMATION';
        }elseif($request->action == 3){
            $act = 'ORDER_DELIVERY_CONFIRMATION';
        }elseif($request->action == 4){
            $act = 'ORDER_CANCELLATION_CONFIRMATION';
        }elseif($request->action == 0){
            $act = 'ORDER_RETAKE_CONFIRMATION';
        }
        notify($order->user, $act, $short_code);
        return back()->withNotify($notify);
    }

    public function orderDetails($id)
    {
        $pageTitle = 'Order Details';
        $order = Order::where('id', $id)->with('user','deposit','deposit.gateway','orderDetail', 'appliedCoupon')->firstOrFail();
        return view('admin.order.order_details', compact('order', 'pageTitle'));
    }

    public function adminSellsLog()
    {
        $emptyMessage  = 'No sales log Found';
        $pageTitle     = "My Sales";
        $logs          = SellLog::when(request()->search,function($q){
                            return $q->where('order_id',request()->search);
                        })->where('seller_id',0)->latest()->paginate(getPaginate());

        return view('admin.order.sell_log', compact('pageTitle','emptyMessage','logs'));
    }
    public function sellerSellsLog()
    {
        $emptyMessage  = 'No sales log found';
        $pageTitle     = "Seller Sales Log";
        $logs          = SellLog::when(request()->search,function($q){
                            return $q->where('order_id',request()->search);
                        })->where('seller_id','!=',0)->latest()->paginate(getPaginate());

        return view('admin.order.sell_log', compact('pageTitle','emptyMessage','logs'));
    }

}
