<?php

namespace App\Http\Controllers\Api;

use App\Enums\CartStatus;
use App\Exceptions\CheckoutException;
use App\Exceptions\Payment\GatewayError;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User_notification;
use App\Services\AutomaticPaymentService;
use App\Services\Gateways\Paystack;
use App\Traits\OrderManager;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    use OrderManager;

    private AutomaticPaymentService $paymentService;

    public function store(Request $request)
    {
//        $orders = Payment::where('id', 21)->with('orders')->get();
//        return response()->json($orders);
        $request->validate([
            'type' => ['required', 'numeric'],
            'escrow' => ['required', 'boolean'],
            'gateway' => ['required_if:escrow,0', 'string'],
            'callback_url' => ['required_if:escrow,0', 'url'],
        ]);


        if (!Cart::query()->where('user_id', $request->user()->id)->where('status', CartStatus::ACCEPTED)->exists()) {
            return response()->json([
                'status' => 'failed',
                'data' => 'cart is empty'
            ]);
        }

        try {

            if ($request->payment != 1) {
                return response()->json([
                    'status' => 'error',
                    'data' => 'no data'
                ], 400);
            }


            if(!$orders = $this->checkout($request, $request->type)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'You don\'t have enough Money for this order',
                ], 400);
            }

            if($request->get('escrow') == 1){
                $escrow = Escrow::start(
                    $request->user(),
                    $orders
                );

                foreach ($orders as $order) {
                    User_notification::send($request->user(), 'Order placed', $order->id);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'order created, Escrow system initiated',
                    'data' => compact('orders', 'escrow')
                ]);
            }



            $gateway = match ($request->gateway){
                'paystack' => app(Paystack::class),
                default => throw new GatewayError("{$request->gateway} provider not available"),
            };

            $this->paymentService = new AutomaticPaymentService($gateway);

            // Sum up the total_amount from all orders
            $totalAmount = $orders->sum('total_amount');

            $payment = Payment::make($request->user(), $totalAmount, 'paystack', $request->callback_url, 'online payment', $orders);

            $paymentUrl = $this->paymentService->generatePaymentLink($payment);

            foreach ($orders as $order) {
                User_notification::send($request->user(), 'Order placed', $order->id);
            }

            return response()->json([
                'status' => 'success',
                'data' => compact('paymentUrl', 'orders')
            ]);

        } catch (CheckoutException $e) {
            return response(status: 400)->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => $orders
            ]);
        }
    }
}
