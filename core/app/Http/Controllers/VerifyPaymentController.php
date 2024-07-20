<?php

namespace App\Http\Controllers;

use App\Exceptions\Payment\GatewayError;
use App\Models\Payment;
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
            if($payment->isPaid()){
                throw new GatewayError('Invalid payment reference');
            }
            if (!$this->paymentService->confirmPayment($payment)) {
                throw new GatewayError('Invalid payment reference');
            }
            DB::transaction(function () use ($payment) {
                $payment->verify();
                $payment->payable->wallet->deposit($payment->amount, [
                    'description' => $payment->data['description'],
                    'payment_reference' => $payment->reference
                ]);

            });
            return $this->redirectToCallbackUrl($payment, 'success');
        } catch (GatewayError $e) {
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
