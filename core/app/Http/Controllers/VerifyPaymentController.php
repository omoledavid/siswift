<?php

namespace App\Http\Controllers;

use App\Exceptions\Payment\GatewayError;
use App\Models\Payment;
use App\Models\User;
use App\Services\AutomaticPaymentService;
use App\Services\Gateways\Paystack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class VerifyPaymentController extends Controller
{
    private AutomaticPaymentService $paymentService;

    public function __construct()
    {
        $this->paymentService = new AutomaticPaymentService(app(Paystack::class));
    }

    /**
     * @throws GatewayError
     */
    public function __invoke(Request $request, Payment $payment)
    {
        //TODO: Use webhook for payment verification instead

        try {
            if ($payment->isPaid()) {
                throw new GatewayError('Invalid payment reference');
            }

            $gateway = match ($payment->gateway) {
                'paystack' => app(Paystack::class),
                default => throw new GatewayError("{$request->gateway} provider not available"),
            };

            $this->paymentService = new AutomaticPaymentService($gateway);

            if (!$this->paymentService->confirmPayment($payment)) {
                throw new GatewayError('Invalid payment reference');
            }

            // Use DB transaction to handle payment verification and subsequent actions
            DB::transaction(function () use ($payment) {
                $payment->verify();

                // Sequential check for orders, plan data, and fallback to wallet deposit
                if ($payment?->order_id != null) {
                    // If there are orders, update the payment status for each
                    foreach ($payment->orders as $order) {
                        $order->payment_status = 1;
                        $order->save();
                    }
                } elseif ($payment?->plan_id != null) {
                    // If there's a plan, subscribe the user to the plan
                    $user = User::find($payment->payable_id);
                    $plan = app('rinvex.subscriptions.plan')->find($payment->plan->id);
                    $user->newPlanSubscription($plan->name, $plan);
                } else {
                    // If neither orders nor plan exist, deposit the payment into the wallet
                    $payment->payable->wallet->deposit($payment->amount, [
                        'description' => $payment->data['description'],
                        'payment_reference' => $payment->reference,
                    ]);
                }

            });

            // Redirect to success callback URL after transaction is successful
            return $this->redirectToCallbackUrl($payment, 'success');

        } catch (GatewayError $e) {
            // Handle any gateway error and redirect to failure callback URL
            return $this->redirectToCallbackUrl($payment, 'failed');
        }
    }


    private function redirectToCallbackUrl($payment, $status)
    {
        // Base URL
        $baseUrl = $payment->data['callback_url'];

        // Parameters to append
        $parameters = [
            'status' => $status,
            'reference' => $payment->reference,
        ];

        // Build full URL with query parameters
        $fullUrl = $baseUrl . '?' . http_build_query($parameters);

        // Redirect to the constructed URL
        return Redirect::away($fullUrl);
    }
}
