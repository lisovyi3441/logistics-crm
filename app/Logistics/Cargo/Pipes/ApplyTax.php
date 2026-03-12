<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\Tariff;
use Closure;

/**
 * Calculates and adds tax (VAT) to the total price.
 */
class ApplyTax
{
    public function handle(PricingData $data, Closure $next): PricingData
    {
        // Get tax rate (VAT) from tariff settings
        $tariff = Tariff::select(['tax_rate_percent'])->first() ?? new Tariff;
        $multiplier = ($tariff->tax_rate_percent ?? 20.00) / 100;

        // Tax is calculated from the subtotal of all price components minus discounts
        $subtotal = $data->basePriceCents + $data->surchargeCents + $data->insuranceFeeCents - $data->discountCents;
        $data->setTaxCents($subtotal * $multiplier);

        // Finalize total price calculation
        $data->calculateFinalPrice();

        return $next($data);
    }
}
