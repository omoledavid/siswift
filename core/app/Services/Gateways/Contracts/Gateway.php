<?php

namespace App\Services\Gateways\Contracts;

use App\DataObjects\PaymentData;
use App\Exceptions\Payments\GatewayError;

interface Gateway
{
    /**
     * @param int $amount
     * @param string $email
     * @param $reference
     * @param string|null $callback_url
     * @return string
     * @throws GatewayError
     */
    public function createPaymentLink(float $amount, string $email, $reference, ?string $callback_url): string;

    /**
     * @param string $reference
     * @return PaymentData
     * @throws GatewayError
     */
    public function getPaymentInfo(string $reference): PaymentData;
}
