<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProviderInterface;
use App\Models\Integration;
use App\Models\Store;
use InvalidArgumentException;

class PaymentService
{
    /**
     * Resolve provider adapter with store-specific decrypted credentials
     */
    public function getProvider(string $key, ?Store $store = null): PaymentProviderInterface
    {
        $currentStore = $store ?? (app()->bound('current_store') ? app('current_store') : null);
        $credentials = [];

        if ($currentStore) {
            $integration = Integration::where('store_id', (string) $currentStore->_id)
                ->where('provider_key', $key)
                ->first();

            if ($integration) {
                $credentials = $integration->credentials;
            }
        }

        return match ($key) {
            'razorpay' => new RazorpayPaymentProvider($credentials),
            'cashfree' => new CashfreePaymentProvider($credentials),
            'cod', 'upi', 'card', 'demo' => new CodPaymentProvider(),
            default => new CodPaymentProvider(),
        };
    }

    /**
     * List all supported payment providers with status for current store
     */
    public function getAvailableProviders(?Store $store = null): array
    {
        $currentStore = $store ?? (app()->bound('current_store') ? app('current_store') : null);
        $connectedKeys = [];

        if ($currentStore) {
            $connectedKeys = Integration::where('store_id', (string) $currentStore->_id)
                ->where('is_connected', true)
                ->pluck('provider_key')
                ->toArray();
        }

        return [
            [
                'key' => 'razorpay',
                'name' => 'Razorpay',
                'description' => 'Accept UPI (Google Pay, PhonePe, Paytm), Credit/Debit Cards, NetBanking, and Wallets.',
                'is_connected' => in_array('razorpay', $connectedKeys),
                'icon' => 'CreditCard',
                'capabilities' => ['UPI', 'Cards', 'Net Banking', 'Wallets', 'Auto-Refunds'],
            ],
            [
                'key' => 'cashfree',
                'name' => 'Cashfree Payments',
                'description' => 'Fastest checkouts, instant refunds, UPI intent, and auto-collection.',
                'is_connected' => in_array('cashfree', $connectedKeys),
                'icon' => 'Zap',
                'capabilities' => ['UPI Intent', 'Cards', 'Net Banking', 'Instant Refunds'],
            ],
            [
                'key' => 'cod',
                'name' => 'Cash on Delivery (COD)',
                'description' => 'Allow shoppers to pay in cash upon doorstep package delivery.',
                'is_connected' => true, // Native COD support
                'icon' => 'Banknote',
                'capabilities' => ['Doorstep Collection', 'Configurable COD Fee', 'PIN Code Rules'],
            ],
        ];
    }
}
