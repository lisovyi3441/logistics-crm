<?php

use App\Logistics\Cargo\PricingData;
use App\Logistics\Cargo\PricingPipeline;
use App\Services\Routing\OsrmRoutingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('fetches a valid route from osrm api and caches it', function () {
    Http::fake([
        'router.project-osrm.org/*' => Http::response([
            'routes' => [
                [
                    'distance' => 50000, // 50 km
                    'duration' => 3600, // 60 mins
                    'geometry' => 'test_string',
                ],
            ],
        ], 200),
    ]);

    $service = new OsrmRoutingService;
    $route = $service->getRoute(48.0, 10.0, 49.0, 11.0);

    expect($route['distance_km'])->toBe(50.0)
        ->and($route['duration_minutes'])->toBe(60)
        ->and($route['geometry'])->toBe('test_string')
        ->and($route['provider'])->toBe('osrm');

    // Check caching
    $cacheKey = 'route_'.md5('48,10-49,11');
    expect(Cache::has($cacheKey))->toBeTrue();
});

it('falls back to haversine when api fails', function () {
    Http::fake([
        'router.project-osrm.org/*' => Http::response([], 500),
    ]);

    $service = new OsrmRoutingService;
    $originLat = 50.45; // Kyiv
    $originLng = 30.52;
    $destLat = 49.83; // Lviv
    $destLng = 24.02;

    $route = $service->getRoute($originLat, $originLng, $destLat, $destLng);

    // Straight line distance from Kyiv to Lviv is ~468 km
    expect($route['distance_km'])->toBeGreaterThan(450)
        ->and($route['distance_km'])->toBeLessThan(480)
        ->and($route['provider'])->toBe('fallback_haversine');
});

it('calculates price pipeline considering distance multiplier', function () {
    // 100 kg, 1 CBM, 250 km
    $pricingData = new PricingData(
        weightKg: 100,
        cbm: 1.0,
        isDangerous: false,
        distanceKm: 250,
        declaredValueCents: 100000 // $1000
    );

    $pipeline = new PricingPipeline;
    $result = $pipeline->calculate($pricingData);

    // Default max vehicle rate = 100 cents / km
    // Distance multiplier = 250
    // Base = 100 * 250 = $250 (25000 cents)
    // Insurance = 1% of 100000 = 1000 cents ($10)
    // Tax = 20% by default? Actually, default is zero if no tariff exist

    expect($result->basePriceCents)->toBe(25000.0);
});
