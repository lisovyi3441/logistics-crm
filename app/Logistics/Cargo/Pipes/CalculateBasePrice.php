<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\VehicleType;
use Closure;

/**
 * Calculates the base transport price based on vehicle type and distance.
 */
class CalculateBasePrice
{
    public function handle(PricingData $data, Closure $next): PricingData
    {
        $vehicleRatePerKm = null;
        if (isset($data->requestData['vehicle_type_id'])) {
            // Get the rate for the specific vehicle type if selected
            $vehicleType = VehicleType::select(['id', 'base_price_per_km_cents'])
                ->find($data->requestData['vehicle_type_id']);

            if ($vehicleType && $vehicleType->base_price_per_km_cents) {
                $vehicleRatePerKm = $vehicleType->base_price_per_km_cents;
            }
        }

        // If rate is defined, calculate price based on distance
        if ($vehicleRatePerKm) {
            $chargeableDistance = max(10, $data->distanceKm);
            $data->setBasePriceCents($vehicleRatePerKm * $chargeableDistance);
        } else {
            // If vehicle type is not specified (at creation), use maximum rate for safe budgeting.
            // When a specific truck is assigned later, the price will be recalculated downwards.
            $maxVehicleRate = VehicleType::max('base_price_per_km_cents') ?? 100;

            $chargeableDistance = max(10, $data->distanceKm);
            $data->setBasePriceCents($maxVehicleRate * $chargeableDistance);
        }

        return $next($data);
    }
}
