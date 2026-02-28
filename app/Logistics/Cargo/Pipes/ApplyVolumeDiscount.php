<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use Closure;
use App\Logistics\Cargo\PricingData;

class ApplyVolumeDiscount
{
    // Apply a 5% discount if cargo is over 10 CBM
    public const float DISCOUNT_MULTIPLIER = 0.05;
    public const float CBM_THRESHOLD = 10.0;

    public function handle(PricingData $data, Closure $next)
    {
        if ($data->cbm > self::CBM_THRESHOLD) {
            $data->discountCents = ($data->basePriceCents + $data->surchargeCents) * self::DISCOUNT_MULTIPLIER;
        }

        return $next($data);
    }
}
