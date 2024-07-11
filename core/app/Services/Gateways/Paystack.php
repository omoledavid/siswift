<?php

namespace App\Services\Gateways;

use App\DataObjects\PaymentData;
use App\Exceptions\PaymentError;
use App\Exceptions\Payment\GatewayError;
use App\Services\Gateways\Contracts\Gateway;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class Paystack implements Gateway
{
    private string $secret;
    private PendingRequest $client;

    /**
     * @throws PaymentError
     * @throws GatewayError
     */
    public function __construct()
    {
        $this->secret = config('services.paystack.secret_key');

        if(!$this->secret) throw new GatewayError('Paystack service not available');

        $this->client = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->secret,
        ])->baseUrl('https://api.paystack.co/transaction');
    }

    /**
     * @throws GatewayError
     */
    public function createPaymentLink(float $amount, string $email, $reference, ?string $callback_url): string
    {

        $response = $this->client->post('/initialize', [
            'amount' => $amount * 100,
            'email' => $email,
            'callback_url' => $callback_url,
            'reference' => $reference,
        ]);

        if (!$response->successful()) {
            throw new GatewayError("Sorry, for some reason, payment link could not be generated: {$response['message']}");
        }

        return $response['data']['authorization_url'];
    }

    /**
     * @throws GatewayError
     */
    public function getPaymentInfo(string $reference): PaymentData
    {
        $response = $this->client->get("/verify/{$reference}");

        if (!$response->successful()) {
            throw new GatewayError("Sorry, for some reason, payment link could not be generated: {$response['message']}");
        }

        $responseData = $response->json('data');
        $data = collect($responseData)
            ->only(['status', 'reference', 'currency', 'amount', 'fees'])->toArray();
        $data['amount'] = $data['amount'] / 100;
        $data['fees'] = $data['fees'] / 100;
        $data['createdDate'] = $responseData['created_at'];
        $data['paidDate'] = $responseData['paid_at'];

        return PaymentData::fromArray($data);
    }

    public function getClient(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->paystackSecretKey,
        ])->baseUrl('https://api.paystack.co/transaction');

    }

    public function verifyPaymentWebhook(Request $request): bool
    {
        try {
            $expectedSignature = hash_hmac('sha512', $request->getContent(), config('paystack.secret_key'));
            if ($expectedSignature !== $request->header('X-PAYSTACK-SIGNATURE')) {
                throw new \Exception('Invalid webhook signature');
            }

            $event = $request->input('event');
            $data = $request->input('data');

            if ($event === 'charge.success' && $data['status'] === 'success') {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            logger()->error("Webhook payment verification failed: {$e->getMessage()}");
            throw $e;
        }
    }
}
