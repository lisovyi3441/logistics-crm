<?php

declare(strict_types=1);

namespace App\Services\Routing;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OsrmRoutingService implements RoutingServiceInterface
{
    private const BASE_URL = 'http://router.project-osrm.org/route/v1/driving';

    /**
     * Get route details between two coordinates using OSRM.
     *
     * @return array{
     *     distance_km: float,
     *     duration_minutes: int,
     *     geometry: string|array,
     *     provider: string
     * }
     */
    public function getRoute(float $originLat, float $originLng, float $destinationLat, float $destinationLng): array
    {
        // Create a unique hash for these specific coordinates to use as a Redis cache key
        $cacheKey = 'route_'.md5("{$originLat},{$originLng}-{$destinationLat},{$destinationLng}");

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addDay(), function () use ($originLat, $originLng, $destinationLat, $destinationLng) {
            // OSRM expects coordinates in lng,lat format
            $coordinates = "{$originLng},{$originLat};{$destinationLng},{$destinationLat}";

            $url = sprintf('%s/%s?overview=full&geometries=geojson', self::BASE_URL, $coordinates);

            try {
                $response = Http::timeout(5)->get($url);

                if ($response->successful() && isset($response->json()['routes'][0])) {
                    $route = $response->json()['routes'][0];

                    return [
                        // OSRM returns distance in meters, convert to kilometers
                        'distance_km' => round($route['distance'] / 1000, 2),
                        // OSRM returns duration in seconds, convert to minutes
                        'duration_minutes' => (int) round($route['duration'] / 60),
                        // Return GeoJSON geometry for Leaflet mapping on the frontend
                        'geometry' => $route['geometry'],
                        'provider' => 'osrm',
                    ];
                }

                Log::warning('OSRM API failed to return a valid route', [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);

            } catch (\Exception $e) {
                Log::error('OSRM API Request Exception', ['message' => $e->getMessage()]);
            }

            // Fallback: If API fails, return straight-line estimates or defaults
            return $this->getFallbackRoute($originLat, $originLng, $destinationLat, $destinationLng);
        });
    }

    /**
     * Calculate a rough straight-line Euclidean distance fallback if the API is down.
     */
    private function getFallbackRoute(float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        // Haversine formula for rough distance
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * asin(sqrt($a));
        $distance = $earthRadius * $c;

        return [
            'distance_km' => round($distance, 2),
            // Rough estimate: assume 60km/h average speed in a straight line
            'duration_minutes' => (int) round(($distance / 60) * 60),
            'geometry' => [
                'type' => 'LineString',
                'coordinates' => [
                    [$lng1, $lat1],
                    [$lng2, $lat2],
                ],
            ],
            'provider' => 'fallback_haversine',
        ];
    }
}
