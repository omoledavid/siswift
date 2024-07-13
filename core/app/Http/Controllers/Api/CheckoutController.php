<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
use App\Models\Escrow;
use App\Traits\OrderManager;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use OrderManager;

    public function store(Request $request)
    {
        $request->validate([
            'type' => ['required', 'numeric']
        ]);

        try {

            if ($request->payment == 1) {
                $order = $this->checkout($request, $request->type);
                $escrow = Escrow::start(
                    $request->user(),
                    $order
                );
                return response()->json([
                    'status' => 'success',
                    'message' => 'order created, user can make deposit',
                    'data' => compact('order', 'escrow')
                ]);
            }

            return response()->json([
                'status' => 'failed',
                'data' => 'no data'
            ]);
        } catch (CheckoutException $e) {
            return response(status: 400)->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => $order
            ]);
        }
    }
}
