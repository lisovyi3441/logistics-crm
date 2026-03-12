<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use Closure;

/**
 * Applies discount for high-volume orders.
 */
class ApplyVolumeDiscount
{
    // Discount percentage for large volumes (0.05 = 5%).
    public const float DISCOUNT_MULTIPLIER = 0.05;

    // Volume threshold (CBM) after which the discount is applied.
    public const float CBM_THRESHOLD = 10.0;

    public function handle(PricingData $data, Closure $next): PricingData
    {
        if ($data->cbm > self::CBM_THRESHOLD) {
            // Currently using a fixed percentage discount for high volumes (over 10 CBM).
            // The pipeline is ready for progressive scale implementation in the future.
            $data->setDiscountCents(($data->basePriceCents + $data->surchargeCents) * self::DISCOUNT_MULTIPLIER);
        }

        return $next($data);
    }
}
