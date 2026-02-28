<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use Closure;
use App\Logistics\Cargo\PricingData;

class CalculateBasePrice
{
    // E.g. 5 EUR per 1 KG or 100 EUR per 1 CBM, whichever is greater
    public const int PRICE_PER_KG_CENTS = 500;
    public const int PRICE_PER_CBM_CENTS = 10000;

    public function handle(PricingData $data, Closure $next)
    {
        $priceByWeight = $data->weightKg * self::PRICE_PER_KG_CENTS;
        $priceByVolume = $data->cbm * self::PRICE_PER_CBM_CENTS;

        // Charge by whichever metric yields a higher base rate (industry standard volumetric weight)
        $data->basePriceCents = max($priceByWeight, $priceByVolume);

        return $next($data);
    }
}
