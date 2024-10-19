<?php

namespace App\Services;

use App\Exceptions\Payment\GatewayError;
use App\Models\Payment;
use App\Services\Gateways\Contracts\Gateway;
use Illuminate\Support\Collection;

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
    public function generatePaymentLink($payments): string
    {
        // If a single Payment instance is passed, convert it to an array with one element
        if ($payments instanceof Payment) {
            $payments = [$payments]; // Wrap single payment in an array
        } elseif ($payments instanceof Collection) {
            $payments = $payments->toArray(); // Convert Collection to array
        }

        // Sum the amounts for all payments
        $totalAmount = array_reduce($payments, function ($carry, $payment) {
            return $carry + $payment->amount;
        }, 0);

        // Use the reference and email from the first payment
        $firstPayment = $payments[0];

        // Generate the payment link
        return $this->gateway->createPaymentLink(
            $totalAmount,  // Total amount for all payments
            $firstPayment->payable->email,  // Email from the first payment
            $firstPayment->reference,  // Reference from the first payment
            route('payment.confirm', ['payment' => $firstPayment->reference])
        );
    }

    public function generatePaymentLinks(Payment $payment): string
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

        if ($paymentData->reference !== $payment->reference) throw new GatewayError('Invalid payment reference');

        if ((float) $paymentData->amount !== (float) $payment->amount) throw new GatewayError('Amount mismatch');

        if ($paymentData->status !== true) throw new GatewayError('payment failed');


        return true;
    }
}
