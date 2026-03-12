<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\Tariff;
use Closure;

/**
 * Applies surcharge for transporting dangerous goods (ADR).
 */
class ApplyDangerousGoodsSurcharge
{
    public function handle(PricingData $data, Closure $next): PricingData
    {
        if ($data->isDangerous) {
            // Get the surcharge for dangerous goods (ADR)
            $tariff = Tariff::select(['adr_surcharge_percent'])->first() ?? new Tariff;
            $multiplier = ($tariff->adr_surcharge_percent ?? 25.00) / 100;

            $data->setSurchargeCents($data->basePriceCents * $multiplier);
        }

        return $next($data);
    }
}
