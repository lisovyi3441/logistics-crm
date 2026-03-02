<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\Tariff;
use Closure;

class ApplyDangerousGoodsSurcharge
{
    public function handle(PricingData $data, Closure $next)
    {
        if ($data->isDangerous) {
            $tariff = Tariff::first() ?? new Tariff;
            $multiplier = ($tariff->adr_surcharge_percent ?? 25.00) / 100;

            $data->setSurchargeCents($data->basePriceCents * $multiplier);
        }

        return $next($data);
    }
}
