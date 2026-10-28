<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentProviderInterface
{
    /**
     * Get provider technical key (e.g. 'razorpay')
     */
    public function getKey(): string;

    /**
     * Get human readable provider name
     */
    public function getName(): string;

    /**
     * Declared capabilities for graceful feature degradation
     */
    public function getCapabilities(): array;

    /**
     * Initialize/create a payment order or token
     */
    public function createPayment(Order $order, array $options = []): array;

    /**
     * Verify payment status and signature
     */
    public function verifyPayment(array $payload, ?string $signature = null): array;

    /**
     * Process signed incoming webhook
     */
    public function handleWebhook(array $payload, string $signature): array;

    /**
     * Initiate a partial or full refund
     */
    public function refundPayment(Payment $payment, float $amount, string $reason = ''): array;

    /**
     * Test merchant credentials connection
     */
    public function testConnection(array $credentials): array;
}
