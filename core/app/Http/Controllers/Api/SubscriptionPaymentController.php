<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\Payment\GatewayError;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Plan;
use App\Services\AutomaticPaymentService;
use App\Services\Gateways\Paystack;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubscriptionPaymentController extends Controller
{
    private AutomaticPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new AutomaticPaymentService(app(Paystack::class));
    }

    public function __invoke(Request $request)
    {
        $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('id', $request->plan_id)],
            'gateway' => ['required'],
            'callback_url' => ['required', 'url'],
        ]);
        $user = auth()->user();
        $plan = app('rinvex.subscriptions.plan')->find($request->plan_id);
        $amount = $plan->price;
        if($user->wallet->balance >= $amount ){
            $user->wallet->withdraw($amount, [
                'description' => "Subscription Payment",
            ]);
            $user->newPlanSubscription($plan->name, $plan);
            return response()->json([
                'status' => true,
                'message' => 'You\'ve successfully subscribed to '.$plan->name.'(NGN '.$amount.') plan from your wallet balance.',
            ]);
        }


        try {
            $gateway = match ($request->gateway){
                'paystack' => app(Paystack::class),
                default => throw new GatewayError("{$request->gateway} provider not available"),
            };

            $this->paymentService = new AutomaticPaymentService($gateway);

            $plan = Plan::where('id', $request->plan_id)->firstOrFail();
            $payment = Payment::make($request->user(), $amount, 'paystack', $request->callback_url);
            $payment->plan_id = $plan->id;
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
