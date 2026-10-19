<?php

declare(strict_types=1);

use App\Models\VehicleType;

it('creates a vehicle type with proper attributes', function () {
    $vehicleType = VehicleType::factory()->create([
        'name' => 'Standard Semi-Trailer',
        'max_weight_kg' => 24000,
        'max_volume_cbm' => 86.5,
        'base_price_per_km_cents' => 180,
    ]);

    expect($vehicleType->name)->toBe('Standard Semi-Trailer')
        ->and((float) $vehicleType->max_weight_kg)->toBe(24000.0)
        ->and((float) $vehicleType->max_volume_cbm)->toBe(86.5)
        ->and($vehicleType->base_price_per_km_cents)->toBe(180);
});
