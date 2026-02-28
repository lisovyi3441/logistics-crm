<?php

declare(strict_types=1);

use App\Models\Truck;
use App\Rules\MaxPayloadVolumeRule;

it('passes validation when payload and volume are within truck limits', function () {
    $truck = Truck::factory()->create([
        'max_weight_kg' => 10000,
        'max_volume_cbm' => 50,
    ]);

    $items = [
        ['weight_kg' => 5000, 'cbm' => 20],
        ['weight_kg' => 4500, 'cbm' => 25],
    ]; // Total: 9500kg, 45cbm

    $rule = new MaxPayloadVolumeRule($truck->id);

    $passed = true;
    $rule->validate('items', $items, function ($message) use (&$passed) {
        $passed = false;
    });

    expect($passed)->toBeTrue();
});

it('fails validation when payload weight exceeds truck limits', function () {
    $truck = Truck::factory()->create([
        'max_weight_kg' => 10000,
        'max_volume_cbm' => 50,
    ]);

    $items = [
        ['weight_kg' => 8000, 'cbm' => 20],
        ['weight_kg' => 3000, 'cbm' => 20],
    ]; // Total: 11000kg

    $rule = new MaxPayloadVolumeRule($truck->id);

    $passed = true;
    $errorMessage = '';
    $rule->validate('items', $items, function ($message) use (&$passed, &$errorMessage) {
        $passed = false;
        $errorMessage = $message;
    });

    expect($passed)->toBeFalse()
        ->and($errorMessage)->toContain('The total payload weight (11000kg) exceeds');
});

it('fails validation when volume exceeds truck limits', function () {
    $truck = Truck::factory()->create([
        'max_weight_kg' => 10000,
        'max_volume_cbm' => 50,
    ]);

    $items = [
        ['weight_kg' => 1000, 'cbm' => 30],
        ['weight_kg' => 1000, 'cbm' => 25],
    ]; // Total: 55cbm

    $rule = new MaxPayloadVolumeRule($truck->id);

    $passed = true;
    $errorMessage = '';
    $rule->validate('items', $items, function ($message) use (&$passed, &$errorMessage) {
        $passed = false;
        $errorMessage = $message;
    });

    expect($passed)->toBeFalse()
        ->and($errorMessage)->toContain('The total volume (55 cbm) exceeds');
});

it('passes validation if no truck id is given', function () {
    $items = [
        ['weight_kg' => 80000, 'cbm' => 200],
    ];

    $rule = new MaxPayloadVolumeRule(null);

    $passed = true;
    $rule->validate('items', $items, function ($message) use (&$passed) {
        $passed = false;
    });

    expect($passed)->toBeTrue();
});
