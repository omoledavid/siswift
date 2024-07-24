<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Payment\GatewayError;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\AutomaticPaymentService;
use App\Services\Gateways\Paystack;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderPaymentController extends Controller
{
    private AutomaticPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new AutomaticPaymentService(app(Paystack::class));
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'order_id' => ['required', Rule::exists('orders', 'id')->where('user_id', $request->user()->id)->where('payment_status', 0)],
            'gateway' => ['required'],
            'callback_url' => ['required', 'url'],
        ], [
            'amount.required' => 'required',
        ]);


        try {
            $gateway = match ($request->gateway){
                'paystack' => app(Paystack::class),
                default => throw new GatewayError("{$request->gateway} provider not available"),
            };

            $this->paymentService = new AutomaticPaymentService($gateway);

            $order = Order::query()->find($request->order_id);
            $payment = Payment::make($request->user(), $order->total_amount, 'paystack', $request->callback_url);
            $payment->order_id = $order->id;
            $payment->save();
            $paymentUrl = $this->paymentService->generatePaymentLink($payment);
            return response()->json([
                'status' => 'success',
                'data' => compact('paymentUrl', 'payment')
            ]);
        }catch (GatewayError $e) {
            return response()->json([
                'status' => 'failed',
                'message' => $e->getMessage()
            ]);
        }
    }
}
