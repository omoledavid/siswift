<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Payment\GatewayError;
use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\AutomaticPaymentService;
use App\Services\Gateways\Paystack;
use Illuminate\Http\Request;

class HandlePaymentController extends Controller
{
    private AutomaticPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new AutomaticPaymentService(app(Paystack::class));
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'amount' => ['required', 'numeric'],
            'gateway' => ['required'],
            'callback_url' => ['required', 'url'],
        ], [
            'amount.required' => 'required',
        ]);

        if($request->gateway === 'paystack'){
            $gateway = app(Paystack::class);
        }elseif ($request->gateway === 'flutter'){
            return response(404)->json([
                'status' => 'failed',
                'message' => "{$request->gateway} provider not available"
            ]);
        }else{
            return response(404)->json([
                'status' => 'failed',
                'message' => "{$request->gateway} provider not available"
            ]);
        }

        $this->paymentService = new AutomaticPaymentService($gateway);

        try {
            $payment = Payment::make($request->user(), $request->amount, 'paystack', $request->callback_url);
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
