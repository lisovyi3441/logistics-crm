<?php

declare(strict_types=1);

namespace App\Logistics\Cargo\Pipes;

use App\Logistics\Cargo\PricingData;
use App\Models\VehicleType;
use Closure;

class CalculateBasePrice
{
    public function handle(PricingData $data, Closure $next)
    {
        $vehicleRatePerKm = null;
        if (isset($data->requestData['vehicle_type_id'])) {
            $vehicleType = VehicleType::find($data->requestData['vehicle_type_id']);
            if ($vehicleType && $vehicleType->base_price_per_km_cents) {
                $vehicleRatePerKm = $vehicleType->base_price_per_km_cents;
            }
        }

        // If a vehicle type rate is defined, use it directly per km
        if ($vehicleRatePerKm) {
            $chargeableDistance = max(10, $data->distanceKm);
            $data->setBasePriceCents($vehicleRatePerKm * $chargeableDistance);
        } else {
            // Default to the maximum vehicle rate to ensure costs are covered upfront.
            // When an admin assigns a specific, cheaper truck, the price will decrease dynamically.
            $maxVehicleRate = VehicleType::max('base_price_per_km_cents') ?? 100;

            $chargeableDistance = max(10, $data->distanceKm);
            $data->setBasePriceCents($maxVehicleRate * $chargeableDistance);
        }

        return $next($data);
    }
}
