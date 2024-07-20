<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    //
    public function index()
    {
        $user = auth()->user();
        $orders = Order::where('user_id', $user->id)->paginate(10);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No data'], 200);
        }

        return response()->json($orders);
    }

    public function store(Request $request){

    }
    public function show(Order $order){
        return response()->json($order);
    }
    public function pendingOrders($type, $status = null){
        $user = auth()->user();

        if($status === 'seller'){
            $query = OrderDetail::where('seller_id', $user->id);
//            return $query->PendingOrder()->paginate(10);
            if ($type == 'pending') {
                $query = $query->PendingOrder()->with('order');//pending
            } elseif ($type == 'processing') {
                $query = $query->ProcessingOrder();//processing
            } elseif ($type == 'completed') {
                $query = $query->CompletedOrder();//completed
            } elseif ($type == 'canceled') {
                $query = $query->CancelledOrder();//canceled
            }
            $orders = $query->latest()->paginate(10);
            return response()->json([
                'orders' => $orders
            ]);
        }

        $query = Order::where('user_id', $user->id)->whereIn('payment_status', [1, 2]);
        if ($type == 'pending') {
            $query = $query->where('status', 0);//pending
        } elseif ($type == 'processing') {
            $query = $query->where('status', 1);//processing
        } elseif ($type == 'completed') {
            $query = $query->where('status', 3);//completed
        } elseif ($type == 'canceled') {
            $query = $query->where('status', 4);//canceled
        }

        $orders = $query->latest()->paginate(getPaginate());
        return response()->json($orders);
    }
}
