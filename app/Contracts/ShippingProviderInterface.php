<?php

namespace App\Contracts;

use App\Models\Order;

interface ShippingProviderInterface
{
    /**
     * Get provider technical key (e.g. 'shiprocket')
     */
    public function getKey(): string;

    /**
     * Get human readable provider name
     */
    public function getName(): string;

    /**
     * Validate merchant credentials with provider API
     */
    public function testConnection(array $credentials): array;

    /**
     * Retrieve merchant pickup locations
     */
    public function getPickupLocations(array $credentials): array;

    /**
     * Estimate shipping rates between PIN codes
     */
    public function estimateRates(string $pickupPincode, string $deliveryPincode, float $weightKg, bool $isCod = false, float $orderValue = 0): array;

    /**
     * Create shipment draft or order on provider
     */
    public function createShipment(Order $order, array $credentials, array $options = []): array;

    /**
     * Generate AWB code and assign courier
     */
    public function generateAwb(string $shipmentId, array $credentials, ?int $courierId = null): array;

    /**
     * Generate shipping label URL
     */
    public function generateLabel(array $shipmentIds, array $credentials): array;

    /**
     * Fetch live shipment tracking details
     */
    public function trackShipment(string $awbCode, array $credentials): array;

    /**
     * Cancel an active shipment
     */
    public function cancelShipment(array $awbCodes, array $credentials): array;
}
