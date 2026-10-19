<?php

declare(strict_types=1);

use App\Models\Tariff;

it('creates a tariff with proper attributes', function () {
    $tariff = Tariff::create([
        'price_per_km_cents' => 150,
        'insurance_rate_percent' => 1.5,
        'tax_rate_percent' => 20.0,
        'adr_surcharge_percent' => 25.0,
    ]);

    expect($tariff->price_per_km_cents)->toBe(150)
        ->and((float) $tariff->insurance_rate_percent)->toBe(1.5)
        ->and((float) $tariff->tax_rate_percent)->toBe(20.0)
        ->and((float) $tariff->adr_surcharge_percent)->toBe(25.0);
});
