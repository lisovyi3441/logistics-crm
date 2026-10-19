<?php

declare(strict_types=1);

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Truck;
use App\Models\VehicleType;

it('belongs to vehicle type', function () {
    $vehicleType = VehicleType::factory()->create();
    $truck = Truck::factory()->create(['vehicle_type_id' => $vehicleType->id]);

    expect($truck->vehicleType)->toBeInstanceOf(VehicleType::class)
        ->and($truck->vehicleType->id)->toBe($vehicleType->id);
});

it('has orders and active orders relationships', function () {
    $truck = Truck::factory()->create();

    $inTransitOrder = Order::factory()->create([
        'truck_id' => $truck->id,
        'status' => OrderStatus::IN_TRANSIT,
    ]);

    Order::factory()->create([
        'truck_id' => $truck->id,
        'status' => OrderStatus::DELIVERED,
    ]);

    expect($truck->orders)->toHaveCount(2)
        ->and($truck->activeOrders)->toHaveCount(1)
        ->and($truck->activeOrders->first()->id)->toBe($inTransitOrder->id);
});

it('correctly determines if truck is busy', function () {
    $truck = Truck::factory()->create();

    expect($truck->isBusy())->toBeFalse();

    $order = Order::factory()->create([
        'truck_id' => $truck->id,
        'status' => OrderStatus::IN_TRANSIT,
    ]);

    expect($truck->fresh()->isBusy())->toBeTrue();

    $order->update(['status' => OrderStatus::DELIVERED]);

    expect($truck->fresh()->isBusy())->toBeFalse();
});
