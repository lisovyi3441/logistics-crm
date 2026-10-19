<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Truck;
use App\Rules\TruckNotBusyRule;

it('passes validation when the truck is not in transit', function () {
    $truck = Truck::factory()->create();

    // Completed order
    Order::factory()->create([
        'truck_id' => $truck->id,
        'status' => OrderStatus::DELIVERED,
    ]);

    $rule = new TruckNotBusyRule;

    $failed = false;
    $rule->validate('truck_id', $truck->id, function () use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse();
});

it('fails validation when the truck has an in-transit order', function () {
    $truck = Truck::factory()->create();

    $order = Order::factory()->create([
        'truck_id' => $truck->id,
        'status' => OrderStatus::IN_TRANSIT,
        'order_number' => 'ORD-12345',
    ]);

    $rule = new TruckNotBusyRule;

    $failed = false;
    $errorMessage = '';
    $rule->validate('truck_id', $truck->id, function ($message) use (&$failed, &$errorMessage) {
        $failed = true;
        $errorMessage = $message;
    });

    expect($failed)->toBeTrue()
        ->and($errorMessage)->toBe('The selected truck is currently busy executing order #ORD-12345.');
});
