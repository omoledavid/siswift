<?php

namespace App\Http\Controllers\Api;

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
        $request->validate([
            'type' => ['required', 'numeric'],
            'escrow' => ['required', 'boolean'],
            'gateway' => ['required_if:escrow,0', 'string'],
            'callback_url' => ['required_if:escrow,0', 'url'],
        ]);


        if (!Cart::query()->where('user_id', $request->user()->id)->exists()) {
            return response()->json([
                'status' => 'failed',
                'data' => 'cart is empty'
            ]);
        }

        try {

            if ($request->payment != 1) {
                return response()->json([
                    'status' => 'failed',
                    'data' => 'no data'
                ]);
            }


            if(!$order = $this->checkout($request, $request->type)){
                return response()->json([
                    'status' => 'error',
                    'message' => 'You don\'t have enough Money for this order',
                ], 400);
            }

            if($request->get('escrow') == 1){
                $escrow = Escrow::start(
                    $request->user(),
                    $order
                );

                User_notification::send($request->user(), 'Order placed');

                return response()->json([
                    'status' => 'success',
                    'message' => 'order created, Escrow system initiated',
                    'data' => compact('order', 'escrow')
                ]);
            }



            $gateway = match ($request->gateway){
                'paystack' => app(Paystack::class),
                default => throw new GatewayError("{$request->gateway} provider not available"),
            };

            $this->paymentService = new AutomaticPaymentService($gateway);

            $payment = Payment::make($request->user(), $order->total_amount, 'paystack', $request->callback_url, order: $order);

            $paymentUrl = $this->paymentService->generatePaymentLink($payment);

            User_notification::send($request->user(), 'Order placed');

            return response()->json([
                'status' => 'success',
                'data' => compact('paymentUrl', 'order')
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
