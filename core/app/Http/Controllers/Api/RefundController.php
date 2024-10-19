<?php

namespace App\Http\Controllers\Api;

use App\Enums\RefundStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Refund;
use Illuminate\Http\Request;

class RefundController extends Controller
{
    public function refund()
    {
        $refund = Refund::where('user_id', auth()->id())->with('disputes', 'disputes.replies')->get();
        return response()->json([
            'status' => true,
            'data' => $refund
        ]);
    }
    public function requestRefund(Request $request, $orderId)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'reason' => 'required|string|max:255',
        ]);

        $order = Order::findOrFail($orderId);

        // Check if the user is the buyer
        if (auth()->id() !== $order->user_id) {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        $refund = Refund::create([
            'order_id' => $order->id,
            'amount' => $request->amount,
            'user_id' => auth()->id(),
            'reason' => $request->reason,
            'status' => RefundStatus::PENDING,
        ]);

        return response()->json($refund, 201);
    }

    public function approveRefund($refundId)
    {
        $refund = Refund::findOrFail($refundId);
        $refund->update(['status' => 'approved']);

        return response()->json($refund);
    }

    public function rejectRefund($refundId)
    {
        $refund = Refund::findOrFail($refundId);
        $refund->update(['status' => 'rejected']);

        return response()->json($refund);
    }
}
