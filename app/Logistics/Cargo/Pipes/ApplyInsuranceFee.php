<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\Tariff;
use Closure;

class ApplyInsuranceFee
{
    public function handle(PricingData $data, Closure $next)
    {
        if ($data->declaredValueCents > 0) {
            $tariff = Tariff::first() ?? new Tariff;
            $multiplier = ($tariff->insurance_rate_percent ?? 1.00) / 100;

            $data->setInsuranceFeeCents($data->declaredValueCents * $multiplier);
        }

        return $next($data);
    }
}
