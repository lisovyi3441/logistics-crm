<?php

declare(strict_types=1);

use App\Models\Order;
use App\Models\OrderItem;

it('belongs to an order', function () {
    $order = Order::factory()->create();
    $item = OrderItem::factory()->create(['order_id' => $order->id]);

    expect($item->order)->toBeInstanceOf(Order::class)
        ->and($item->order->id)->toBe($order->id);
});

it('casts attributes properly', function () {
    $item = OrderItem::factory()->create([
        'quantity' => '5',
        'weight_kg' => '12.50',
        'declared_value_cents' => '2500',
    ]);

    expect($item->quantity)->toBeInt()->toBe(5)
        ->and($item->weight_kg)->toBeFloat()->toBe(12.5)
        ->and($item->declared_value_cents)->toBeInt()->toBe(2500);
});
