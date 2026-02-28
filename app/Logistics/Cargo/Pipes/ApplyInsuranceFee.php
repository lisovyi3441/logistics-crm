<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use Closure;
use App\Logistics\Cargo\PricingData;

class ApplyInsuranceFee
{
    // 1% insurance fee of the declared value
    public const float INSURANCE_FEE_MULTIPLIER = 0.01;

    public function handle(PricingData $data, Closure $next)
    {
        if ($data->declaredValueCents > 0) {
            $data->insuranceFeeCents = $data->declaredValueCents * self::INSURANCE_FEE_MULTIPLIER;
        }

        return $next($data);
    }
}
