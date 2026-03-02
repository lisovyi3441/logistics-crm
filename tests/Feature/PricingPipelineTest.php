<?php

declare(strict_types=1);

use App\Logistics\Cargo\PricingData;
use App\Logistics\Cargo\PricingPipeline;
use App\Models\Tariff;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    Tariff::create([
        'insurance_rate_percent' => 1.00,
        'tax_rate_percent' => 20.00,
        'adr_surcharge_percent' => 25.00,
    ]);
});

it('calculates base price correctly using default max vehicle rate if none selected', function () {
    VehicleType::create(['name' => 'Small', 'max_weight_kg' => 1000, 'max_volume_cbm' => 10, 'base_price_per_km_cents' => 50]);
    VehicleType::create(['name' => 'Large', 'max_weight_kg' => 20000, 'max_volume_cbm' => 80, 'base_price_per_km_cents' => 100]);

    // Distance 50km
    $data = new PricingData(weightKg: 100, cbm: 1, isDangerous: false, distanceKm: 50);
    $pipeline = new PricingPipeline;

    $result = $pipeline->calculate($data);

    // Max rate is 100. Distance = 50. 100 * 50 = 5000 cents.
    expect($result->basePriceCents)->toBe(5000.0);
});

it('calculates base price correctly using selected vehicle type', function () {
    $vt = VehicleType::create(['name' => 'Small', 'max_weight_kg' => 1000, 'max_volume_cbm' => 10, 'base_price_per_km_cents' => 60]);

    // Distance 50km
    $data = new PricingData(weightKg: 100, cbm: 1, isDangerous: false, distanceKm: 50);
    $data->requestData = ['vehicle_type_id' => $vt->id];
    $pipeline = new PricingPipeline;

    $result = $pipeline->calculate($data);

    // 60 * 50 = 3000 cents.
    expect($result->basePriceCents)->toBe(3000.0);
});

it('applies dangerous goods surcharge correctly based on vehicle base price', function () {
    VehicleType::create(['name' => 'Small', 'max_weight_kg' => 1000, 'max_volume_cbm' => 10, 'base_price_per_km_cents' => 100]);
    // 100 * 50km = 5000 base
    $data = new PricingData(weightKg: 100, cbm: 1, isDangerous: true, distanceKm: 50);
    $pipeline = new PricingPipeline;

    $result = $pipeline->calculate($data);

    // ADR Surcharge = 25% of 5000 = 1250 cents.
    expect($result->surchargeCents)->toBe(1250.0);
});

it('calculates complex final price with all modifiers', function () {
    $vt = VehicleType::create(['name' => 'Large', 'max_weight_kg' => 20000, 'max_volume_cbm' => 80, 'base_price_per_km_cents' => 100]);

    // Distance 100km -> Base: 10,000 cents.
    // Dangerous -> ADR Surcharge 25% of 10k = 2,500 cents.
    // Insured -> Declared 50,000 cents -> 1% = 500 cents.
    // Volume Discount applies if > 10 CBM -> length is 20 CBM -> -5% discount of (Base 10k + Surcharge 2.5k) = 625 cents.
    // Subtotal: 10000 + 2500 + 500 - 625 = 12,375 cents.
    // Tax -> 20% of 12,375 = 2,475 cents.
    // Final = 14,850 cents.

    $data = new PricingData(weightKg: 100, cbm: 20, isDangerous: true, distanceKm: 100, declaredValueCents: 50000);
    $data->requestData = ['vehicle_type_id' => $vt->id];
    $pipeline = new PricingPipeline;

    $result = $pipeline->calculate($data);

    expect($result->basePriceCents)->toBe(10000.0)
        ->and($result->insuranceFeeCents)->toBe(500.0)
        ->and($result->surchargeCents)->toBe(2500.0)
        ->and($result->discountCents)->toBe(625.0)
        ->and($result->taxCents)->toBe(2475.0)
        ->and($result->finalPriceCents)->toBe(14850.0);
});
