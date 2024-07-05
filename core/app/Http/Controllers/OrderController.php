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
use App\Traits\OrderManager;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    use OrderManager;

    public function __construct()
    {
        $this->activeTemplate = activeTemplate();
    }

    public function orders($type)
    {
        $pageTitle = ucfirst($type) . ' Orders';
        $emptyMessage = 'No order yet';
        $query = Order::where('user_id', auth()->user()->id)->whereIn('payment_status', [1, 2]);
        if ($type == 'pending') {
            $query = $query->where('status', 0);
        } elseif ($type == 'processing') {
            $query = $query->where('status', 1);
        } elseif ($type == 'dispatched') {
            $query = $query->where('status', 2);
        } elseif ($type == 'completed') {
            $query = $query->where('status', 3);
        } elseif ($type == 'canceled') {
            $query = $query->where('status', 4);
        }

        $orders = $query->latest()->paginate(getPaginate());
        return view($this->activeTemplate . 'user.orders.index', compact('pageTitle', 'orders', 'emptyMessage', 'type'));
    }

    public function orderDetails($order_number)
    {
        $pageTitle = 'Order Details';
        $order = Order::where('order_number', $order_number)->where('user_id', auth()->user()->id)->with('deposit', 'orderDetail', 'appliedCoupon')->first();

        return view($this->activeTemplate . 'user.orders.details', compact('order', 'pageTitle'));
    }

    public function confirmOrder(Request $request, $type)
    {
        $this->checkout($request, $type);

        if($request->payment === 1){
            return redirect()->route('user.deposit');
        }

        $notify[] = ['success', 'Your order has submitted successfully please wait for a confirmation email'];
        return redirect()->route('user.home')->withNotify($notify);
    }

    public function trackOrder()
    {
        $pageTitle = 'Order Tracking';

        return view($this->activeTemplate . 'order_track', compact('pageTitle'));
    }

    public function getOrderTrackData(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|max:160',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $order_number   = $request->order_number;
        $order_data     = Order::where('order_number', $order_number)->first();
        if ($order_data) {
            $p_status   = $order_data->payment_status;
            $status     = $order_data->status;

            return response()->json(['success' => true, 'payment_status' => $p_status, 'status' => $status]);
        } else {
            $notify = 'No order found';
            return response()->json(['success' => false, 'message' => $notify]);
        }
    }

    public function printInvoice(Order $order)
    {
        $pageTitle = 'Print Invoice';

        return view('invoice.print', compact('pageTitle', 'order'));
    }
}
