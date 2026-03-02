<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\Tariff;
use Closure;

class ApplyTax
{
    public function handle(PricingData $data, Closure $next)
    {
        $tariff = Tariff::first() ?? new Tariff;
        $multiplier = ($tariff->tax_rate_percent ?? 20.00) / 100;

        $subtotal = $data->basePriceCents + $data->surchargeCents + $data->insuranceFeeCents - $data->discountCents;
        $data->setTaxCents($subtotal * $multiplier);

        $data->calculateFinalPrice(); // calculate final inside

        return $next($data);
    }
}
