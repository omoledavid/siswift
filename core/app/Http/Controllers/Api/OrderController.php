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
    public function pendingOrders($type){

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
        return response()->json($orders);
    }
}
