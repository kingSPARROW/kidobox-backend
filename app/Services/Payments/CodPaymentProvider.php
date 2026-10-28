<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProviderInterface;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class CodPaymentProvider implements PaymentProviderInterface
{
    public function getKey(): string
    {
        return 'cod';
    }

    public function getName(): string
    {
        return 'Cash on Delivery (COD)';
    }

    public function getCapabilities(): array
    {
        return [
            'upi' => false,
            'cards' => false,
            'netbanking' => false,
            'wallets' => false,
            'cash' => true,
            'refunds' => true,
            'webhooks' => false,
        ];
    }

    public function testConnection(array $credentials): array
    {
        return ['success' => true, 'message' => 'COD is enabled and active.'];
    }

    public function createPayment(Order $order, array $options = []): array
    {
        return [
            'success' => true,
            'provider' => 'cod',
            'gateway_order_id' => 'cod_' . Str::random(12),
            'amount' => $order->pricing['total'] ?? 0,
            'currency' => $order->currency ?? 'INR',
            'order_id' => (string) $order->_id,
            'requires_collection_on_delivery' => true,
        ];
    }

    public function verifyPayment(array $payload, ?string $signature = null): array
    {
        return [
            'success' => true,
            'gateway_payment_id' => 'cod_collected_' . Str::random(10),
            'gateway_order_id' => $payload['order_id'] ?? null,
            'message' => 'COD order confirmed. Amount payable upon parcel delivery.',
        ];
    }

    public function handleWebhook(array $payload, string $signature): array
    {
        return ['success' => true];
    }

    public function refundPayment(Payment $payment, float $amount, string $reason = ''): array
    {
        return [
            'success' => true,
            'refund_id' => 'cod_rfnd_' . Str::random(8),
            'amount' => $amount,
            'note' => 'Refund registered for bank payout / store credit.',
        ];
    }
}
