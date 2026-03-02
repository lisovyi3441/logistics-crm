<?php

declare(strict_types=1);

namespace App\Services\Routing;

interface RoutingServiceInterface
{
    /**
     * Get route details between two coordinates.
     *
     * @return array{
     *     distance_km: float,
     *     duration_minutes: int,
     *     geometry: string|array,
     *     provider: string
     * }
     */
    public function getRoute(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array;
}
