<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\Tariff;
use Closure;

/**
 * Applies insurance fee based on the declared value of the cargo.
 */
class ApplyInsuranceFee
{
    public function handle(PricingData $data, Closure $next): PricingData
    {
        if ($data->declaredValueCents > 0) {
            // Get the rate for insurance calculation (usually 1% of the value)
            $tariff = Tariff::select(['insurance_rate_percent'])->first() ?? new Tariff;
            $multiplier = ($tariff->insurance_rate_percent ?? 1.00) / 100;

            $data->setInsuranceFeeCents($data->declaredValueCents * $multiplier);
        }

        return $next($data);
    }
}
