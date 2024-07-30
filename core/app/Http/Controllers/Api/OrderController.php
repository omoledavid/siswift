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
        $orders = Order::where('user_id', $user->id)->where('payment_status', '!=', 1)->paginate(10);

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
    public function pendingOrders($type, $status = null) {
        $user = auth()->user();

        if ($status === 'seller') {
            $query = OrderDetail::where('seller_id', $user->seller_id);

            switch ($type) {
                case 'pending':
                    $query = $query;
                    break;
                case 'processing':
                    $query = $query->ProcessingOrder();
                    break;
                case 'completed':
                    $query = $query->CompletedOrder();
                    break;
                case 'canceled':
                    $query = $query->CancelledOrder();
                    break;
                default:
                    return response()->json(['error' => 'Invalid type provided'], 400);
            }

            $orders = $query->latest()->paginate(10);

            return response()->json([
                'orders' => $orders
            ]);
        }

        $query = Order::where('user_id', $user->id)
            ->whereIn('payment_status', [0, 1]);

        switch ($type) {
            case 'pending':
                $query = $query->where('status', 0);
                break;
            case 'completed':
                $query = $query->where('status', 1);
                break;
            case 'canceled':
                $query = $query->where('status', 4);
                break;
            default:
                return response()->json(['error' => 'Invalid type provided'], 400);
        }

        $orders = $query->latest()->paginate(getPaginate());

        return response()->json($orders);
    }

}
