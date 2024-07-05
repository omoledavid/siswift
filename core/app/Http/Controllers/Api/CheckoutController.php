<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\CheckoutException;
use App\Http\Controllers\Controller;
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
            $order = $this->checkout($request, $request->type);

            if ($request->payment === 1) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'order created, user can make deposit',
                    'data' => $order
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $order
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
