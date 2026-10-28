<?php

namespace App\Services;

use App\Models\Store;

class TaxService
{
    /**
     * Calculate Indian GST breakdown for an order/cart
     * 
     * @param float $subtotal Amount before or after tax depending on mode
     * @param float $taxRate Percentage rate, e.g. 18.0
     * @param string $storeState State where store/origin warehouse is located
     * @param string $destinationState State where customer shipping address is located
     * @param bool $isInclusive Whether prices are tax-inclusive
     * @return array
     */
    public function calculateGst(
        float $subtotal,
        float $taxRate = 18.0,
        string $storeState = 'Maharashtra',
        string $destinationState = 'Maharashtra',
        bool $isInclusive = true
    ): array {
        if ($subtotal <= 0 || $taxRate <= 0) {
            return [
                'taxable_amount' => round($subtotal, 2),
                'cgst' => 0.0,
                'sgst' => 0.0,
                'igst' => 0.0,
                'total_tax' => 0.0,
                'is_interstate' => false,
                'is_inclusive' => $isInclusive,
                'tax_rate' => $taxRate,
            ];
        }

        // Clean state names for comparison
        $origin = strtolower(trim($storeState));
        $dest = strtolower(trim($destinationState));
        $isInterState = ($origin !== '' && $dest !== '' && $origin !== $dest);

        $totalTax = 0.0;
        $taxableAmount = 0.0;

        if ($isInclusive) {
            // Price = Taxable + (Taxable * Rate / 100) => Taxable = Price / (1 + Rate / 100)
            $taxableAmount = round($subtotal / (1.0 + ($taxRate / 100.0)), 2);
            $totalTax = round($subtotal - $taxableAmount, 2);
        } else {
            $taxableAmount = round($subtotal, 2);
            $totalTax = round(($taxableAmount * $taxRate) / 100.0, 2);
        }

        if ($isInterState) {
            // Inter-state: 100% IGST
            return [
                'taxable_amount' => $taxableAmount,
                'cgst' => 0.0,
                'sgst' => 0.0,
                'igst' => $totalTax,
                'total_tax' => $totalTax,
                'is_interstate' => true,
                'is_inclusive' => $isInclusive,
                'tax_rate' => $taxRate,
            ];
        }

        // Intra-state: Split 50-50 into CGST and SGST
        $halfTax = round($totalTax / 2.0, 2);
        $remainder = round($totalTax - ($halfTax * 2), 2); // Avoid floating point rounding loss

        return [
            'taxable_amount' => $taxableAmount,
            'cgst' => $halfTax,
            'sgst' => round($halfTax + $remainder, 2),
            'igst' => 0.0,
            'total_tax' => $totalTax,
            'is_interstate' => false,
            'is_inclusive' => $isInclusive,
            'tax_rate' => $taxRate,
        ];
    }
}
