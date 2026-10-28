<?php

namespace App\Services\Integrations;

class IntegrationCatalog
{
    /**
     * Get the complete platform integration catalog
     */
    public static function getCatalog(): array
    {
        return [
            // PAYMENTS
            [
                'key' => 'razorpay',
                'name' => 'Razorpay',
                'category' => 'payments',
                'badge' => 'Recommended in India',
                'description' => 'The leading payments solution in India. Accept UPI, RuPay, Visa, Mastercard, NetBanking, and Wallets seamlessly.',
                'documentation_url' => 'https://razorpay.com/docs',
                'fields' => [
                    ['name' => 'key_id', 'label' => 'Key ID', 'type' => 'text', 'placeholder' => 'rzp_test_...', 'required' => true],
                    ['name' => 'key_secret', 'label' => 'Key Secret', 'type' => 'password', 'placeholder' => '••••••••••••', 'required' => true],
                    ['name' => 'webhook_secret', 'label' => 'Webhook Secret', 'type' => 'password', 'placeholder' => '••••••••••••', 'required' => false],
                    ['name' => 'mode', 'label' => 'Environment Mode', 'type' => 'select', 'options' => ['sandbox' => 'Sandbox / Test', 'live' => 'Live Production'], 'required' => true],
                ],
                'capabilities' => ['Instant UPI QR', 'Cards & EMI', 'NetBanking (50+ Banks)', 'Signed Webhooks', 'Automated Refunds'],
            ],
            [
                'key' => 'cashfree',
                'name' => 'Cashfree Payments',
                'category' => 'payments',
                'badge' => 'Instant Payouts',
                'description' => 'High-conversion checkout experience with auto-detecting UPI apps and instant automated refund disbursal.',
                'documentation_url' => 'https://docs.cashfree.com',
                'fields' => [
                    ['name' => 'app_id', 'label' => 'App ID', 'type' => 'text', 'placeholder' => 'CF_APP_...', 'required' => true],
                    ['name' => 'secret_key', 'label' => 'Secret Key', 'type' => 'password', 'placeholder' => '••••••••••••', 'required' => true],
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => ['sandbox' => 'Sandbox', 'production' => 'Production'], 'required' => true],
                ],
                'capabilities' => ['UPI Intent Flow', 'Credit & Debit Cards', 'Pay Later / BNPL', 'Instant Refunds'],
            ],

            // SHIPPING
            [
                'key' => 'shiprocket',
                'name' => 'Shiprocket',
                'category' => 'shipping',
                'badge' => 'Essential for India',
                'description' => 'India’s top shipping aggregator connecting you with 25+ courier partners, lower rates, and auto AWB generation.',
                'documentation_url' => 'https://shiprocket.freshdesk.com',
                'fields' => [
                    ['name' => 'email', 'label' => 'Registered Shiprocket Email', 'type' => 'email', 'placeholder' => 'merchant@company.com', 'required' => true],
                    ['name' => 'password', 'label' => 'API Password', 'type' => 'password', 'placeholder' => '••••••••••••', 'required' => true],
                    ['name' => 'pickup_location_name', 'label' => 'Default Pickup Location Name', 'type' => 'text', 'placeholder' => 'Primary Warehouse', 'required' => false],
                ],
                'capabilities' => ['28,000+ PIN Codes', 'AWB Auto-Generation', 'Print Shipping Labels', 'Scheduled Courier Pickups', 'Real-Time Tracking'],
            ],
            [
                'key' => 'delhivery',
                'name' => 'Delhivery Direct',
                'category' => 'shipping',
                'badge' => 'Direct Courier',
                'description' => 'Integrate directly with Delhivery enterprise API for B2C parcel logistics and reverse returns.',
                'documentation_url' => 'https://delhivery.com',
                'fields' => [
                    ['name' => 'client_id', 'label' => 'Client ID', 'type' => 'text', 'placeholder' => 'DELHIVERY_...', 'required' => true],
                    ['name' => 'api_token', 'label' => 'API Token', 'type' => 'password', 'placeholder' => '••••••••••••', 'required' => true],
                ],
                'capabilities' => ['Surface & Express Air', 'Reverse Pickups', 'NDR Management'],
            ],

            // MARKETING & COMMUNICATIONS
            [
                'key' => 'whatsapp_business',
                'name' => 'WhatsApp Business Notifications',
                'category' => 'marketing',
                'badge' => 'High Engagement',
                'description' => 'Send automatic order confirmations, shipping updates, and abandoned cart nudges via official WhatsApp Cloud API.',
                'documentation_url' => 'https://developers.facebook.com/docs/whatsapp',
                'fields' => [
                    ['name' => 'phone_number_id', 'label' => 'Phone Number ID', 'type' => 'text', 'placeholder' => '102345678901234', 'required' => true],
                    ['name' => 'access_token', 'label' => 'System User Access Token', 'type' => 'password', 'placeholder' => 'EAAG...', 'required' => true],
                ],
                'capabilities' => ['Order Placed Alerts', 'Shipping Tracking Updates', 'Abandoned Cart Recovery', 'Interactive Buttons'],
            ],
            [
                'key' => 'mailchimp',
                'name' => 'Mailchimp Newsletter & Campaigns',
                'category' => 'marketing',
                'badge' => 'Email Marketing',
                'description' => 'Sync your customer lists, segment buyers by lifetime spend, and automate welcome discounts.',
                'documentation_url' => 'https://mailchimp.com/developer',
                'fields' => [
                    ['name' => 'api_key', 'label' => 'API Key', 'type' => 'password', 'placeholder' => 'md-xxxxxxxxx-us1', 'required' => true],
                    ['name' => 'list_id', 'label' => 'Audience List ID', 'type' => 'text', 'placeholder' => 'a1b2c3d4e5', 'required' => true],
                ],
                'capabilities' => ['Customer List Sync', 'Welcome Email Series', 'Post-Purchase Campaigns'],
            ],

            // ANALYTICS
            [
                'key' => 'google_analytics',
                'name' => 'Google Analytics 4 (GA4)',
                'category' => 'analytics',
                'badge' => 'Free Analytics',
                'description' => 'Track storefront visitors, product views, add-to-cart events, and completed purchases with enhanced ecommerce.',
                'documentation_url' => 'https://analytics.google.com',
                'fields' => [
                    ['name' => 'measurement_id', 'label' => 'Measurement ID', 'type' => 'text', 'placeholder' => 'G-XXXXXXXXXX', 'required' => true],
                ],
                'capabilities' => ['Pageview Tracking', 'Enhanced Ecommerce', 'Checkout Funnel Analysis', 'Traffic Source Attribution'],
            ],
        ];
    }
}
