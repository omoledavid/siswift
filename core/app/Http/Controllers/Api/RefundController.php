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
        $user = auth()->user();
        $refund = Refund::where('user_id', $user->id)->orWhere('seller_id', $user->seller_id)->with('disputes', 'disputes.replies','disputes.user', 'buyer', 'seller', 'order')->orderBy('id', 'desc')->get();
        return response()->json([
            'status' => true,
            'data' => $refund
        ]);
    }
    public function requestRefund(Request $request, $orderId)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'seller_id' => 'required|exists:users,seller_id',
            'desc' => 'nullable|string',
            'add_info' => 'nullable|string',
            'conclusion' => 'nullable|string',
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
            'seller_id' => $request->seller_id,
            'reason' => $request->reason,
            'desc' => $request->desc,
            'add_info' => $request->add_info,
            'conclusion' => $request->conclusion,
            'status' => RefundStatus::OPEN,
        ]);

        return response()->json($refund, 201);
    }
    public function show(Refund $refund)
    {
        return response()->json([
            'status' => true,
            'data' => $refund->load('disputes', 'disputes.replies','disputes.user', 'seller', 'buyer', 'order')
        ]);
    }

    public function closeRefund(Request $request, $refundId)
    {
        $request->validate([
            'conclusion' => 'nullable|string',
        ]);
        $refund = Refund::findOrFail($refundId);
        $refund->update([
            'status' => RefundStatus::CLOSE,
            'conclusion' => $request->conclusion
        ]);

        return response()->json([
            'status' => true,
            'data' => $refund->load('disputes', 'disputes.replies','disputes.user', 'seller', 'buyer', 'order')
        ]);
    }
}
