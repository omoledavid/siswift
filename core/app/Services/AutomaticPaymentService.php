<?php

namespace App\Services;

use App\Exceptions\Payment\GatewayError;
use App\Models\Payment;
use App\Services\Gateways\Contracts\Gateway;

class AutomaticPaymentService
{
    private $gateway;

    public function __construct(Gateway $gateway)
    {
        $this->gateway = $gateway;
    }

    /**
     * @throws GatewayError
     */
    public function generatePaymentLink(Payment $payment): string
    {
        return $this->gateway->createPaymentLink(
          $payment->amount,
          $payment->payable->email,
          $payment->reference,
          route('payment.confirm', ['payment' => $payment->reference])
        );
    }

    /**
     * @throws GatewayError
     */
    public function confirmPayment(Payment $payment): bool
    {
        $paymentData = $this->gateway->getPaymentInfo($payment->reference);

        if($paymentData->reference !== $payment->reference) throw new GatewayError('Invalid payment reference');

        if((float) $paymentData->amount !== (float) $payment->amount) throw new GatewayError('Amount mismatch');

        if($paymentData->status !== true) throw new GatewayError('payment failed');


        return true;
    }
}
