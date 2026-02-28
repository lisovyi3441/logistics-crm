<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use Closure;

class ApplyTax
{
    // VAT 23%
    public const VAT_MULTIPLIER = 0.23;

    public function handle(PricingData $data, Closure $next)
    {
        $subtotal = $data->basePriceCents + $data->surchargeCents + $data->insuranceFeeCents - $data->discountCents;
        $data->taxCents = $subtotal * self::VAT_MULTIPLIER;

        $data->calculateFinalPrice(); // calculate final inside

        return $next($data);
    }
}
