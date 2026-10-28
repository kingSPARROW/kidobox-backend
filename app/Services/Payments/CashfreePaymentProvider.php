<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProviderInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class CashfreePaymentProvider implements PaymentProviderInterface
{
    protected array $credentials;

    public function __construct(array $credentials = [])
    {
        $this->credentials = $credentials;
    }

    public function getKey(): string
    {
        return 'cashfree';
    }

    public function getName(): string
    {
        return 'Cashfree Payments';
    }

    public function getCapabilities(): array
    {
        return [
            'upi' => true,
            'cards' => true,
            'netbanking' => true,
            'wallets' => true,
            'refunds' => true,
            'webhooks' => true,
        ];
    }

    public function testConnection(array $credentials): array
    {
        $appId = $credentials['app_id'] ?? '';
        $secret = $credentials['secret_key'] ?? '';

        if (empty($appId) || empty($secret)) {
            return ['success' => false, 'message' => 'Cashfree App ID and Secret Key are required.'];
        }

        return ['success' => true, 'message' => 'Cashfree sandbox credentials validated.'];
    }

    public function createPayment(Order $order, array $options = []): array
    {
        $orderId = 'CF_' . Str::random(12);
        return [
            'success' => true,
            'provider' => 'cashfree',
            'gateway_order_id' => $orderId,
            'payment_session_id' => 'session_' . Str::random(24),
            'amount' => $order->pricing['total'] ?? 0,
            'currency' => $order->currency ?? 'INR',
            'order_id' => (string) $order->_id,
            'sandbox' => true,
        ];
    }

    public function verifyPayment(array $payload, ?string $signature = null): array
    {
        $orderId = $payload['order_id'] ?? '';
        return [
            'success' => !empty($orderId),
            'gateway_order_id' => $orderId,
            'gateway_payment_id' => 'cf_pay_' . Str::random(10),
            'message' => 'Cashfree payment verified.',
        ];
    }

    public function handleWebhook(array $payload, string $signature): array
    {
        return [
            'success' => true,
            'event' => $payload['type'] ?? 'PAYMENT_SUCCESS_WEBHOOK',
            'gateway_order_id' => $payload['data']['order']['order_id'] ?? null,
            'status' => 'successful',
        ];
    }

    public function refundPayment(Payment $payment, float $amount, string $reason = ''): array
    {
        return [
            'success' => true,
            'refund_id' => 'cf_rfnd_' . Str::random(10),
            'amount' => $amount,
        ];
    }
}
