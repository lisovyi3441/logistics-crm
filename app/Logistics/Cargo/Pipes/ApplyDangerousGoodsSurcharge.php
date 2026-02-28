<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use Closure;
use App\Logistics\Cargo\PricingData;

class ApplyDangerousGoodsSurcharge
{
    // Apply a 20% surcharge for hazardous materials
    public const SURCHARGE_MULTIPLIER = 0.20;

    public function handle(PricingData $data, Closure $next)
    {
        if ($data->isDangerous) {
            $data->surchargeCents = $data->basePriceCents * self::SURCHARGE_MULTIPLIER;
        }

        return $next($data);
    }
}
