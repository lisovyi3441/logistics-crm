<?php

declare(strict_types=1);

use App\Logistics\Cargo\PricingData;
use App\Logistics\Cargo\PricingPipeline;
use App\Logistics\Cargo\Pipes\CalculateBasePrice;
use App\Logistics\Cargo\Pipes\ApplyDangerousGoodsSurcharge;
use App\Logistics\Cargo\Pipes\ApplyVolumeDiscount;
use App\Logistics\Cargo\Pipes\ApplyTax;

it('calculates base price correctly using weight if it yields higher cost', function () {
    $data = new PricingData(weightKg: 100, cbm: 0.1, isDangerous: false);
    $pipeline = new PricingPipeline();
    
    $result = $pipeline->calculate($data);
    
    // 100 kg * 5.00 EUR = 50,000 cents. CBM would be 0.1 * 100 EUR = 1,000 cents.
    expect($result->basePriceCents)->toBe(50000.0);
});

it('calculates base price correctly using volume if it yields higher cost', function () {
    $data = new PricingData(weightKg: 100, cbm: 20, isDangerous: false);
    $pipeline = new PricingPipeline();
    
    $result = $pipeline->calculate($data);
    
    // 100 kg * 5.00 EUR = 50,000 cents. CBM would be 20 * 100 EUR = 200,000 cents.
    expect($result->basePriceCents)->toBe(200000.0);
});

it('applies dangerous goods surcharge correctly', function () {
    // 100 kg * 5.00 = 50k base
    $data = new PricingData(weightKg: 100, cbm: 0.1, isDangerous: true);
    $pipeline = new PricingPipeline();
    
    $result = $pipeline->calculate($data);
    
    // Surcharge = 20% of 50000 = 10000.
    expect($result->surchargeCents)->toBe(10000.0);
});

it('applies volume discount correctly for high volume cargo', function () {
    // Base volume price = 20 cbm * 100 = 200,000 cents.
    $data = new PricingData(weightKg: 100, cbm: 20, isDangerous: false);
    $pipeline = new PricingPipeline();
    
    $result = $pipeline->calculate($data);
    
    // Discount = 5% of 200,000 = 10,000
    expect($result->discountCents)->toBe(10000.0);
});

it('calculates complex final price with all modifiers', function () {
    // 20 CBM -> Base Price: 200,000 cents
    // Dangerous -> +20% -> 40,000 cents
    // > 10 CBM -> -5% -> 5% of 240,000 = 12,000 cents
    // Subtotal = 200k + 40k - 12k = 228,000 cents
    // Tax -> 23% of 228,000 = 52,440 cents
    // Total = 228,000 + 52,440 = 280,440 cents
    
    $data = new PricingData(weightKg: 100, cbm: 20, isDangerous: true);
    $pipeline = new PricingPipeline();
    
    $result = $pipeline->calculate($data);
    
    expect($result->basePriceCents)->toBe(200000.0)
        ->and($result->surchargeCents)->toBe(40000.0)
        ->and($result->discountCents)->toBe(12000.0)
        ->and($result->taxCents)->toBe(52440.0)
        ->and($result->finalPriceCents)->toBe(280440.0);
});
