<?php

use App\Enums\OrderStatus;
use App\Models\Company;
use App\Models\Order;
use App\Models\User;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->company = Company::factory()->create();
    $this->manager = User::factory()->create(['company_id' => $this->company->id]);
    $this->manager->assignRole('manager');

    $this->order = Order::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'status' => OrderStatus::NEW->value,
    ]);
});

it('allows manager to cancel a new order', function () {
    $response = actingAs($this->manager)
        ->put("/orders/{$this->order->order_number}/status", [
            'status' => OrderStatus::CANCELED->value, // Manager has Permissions::CANCEL_ORDERS
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('orders', [
        'id' => $this->order->id,
        'status' => OrderStatus::CANCELED->value,
    ]);
});

it('forbids manager from changing order to in_transit', function () {
    $response = actingAs($this->manager)
        ->put("/orders/{$this->order->order_number}/status", [
            'status' => OrderStatus::IN_TRANSIT->value,
        ]);

    // ValidationException intercepts because the user has no UPDATE_ORDER_STATUS permission and target is not CANCELED
    $response->assertSessionHasErrors('status');
    $this->assertDatabaseHas('orders', [
        'id' => $this->order->id,
        'status' => OrderStatus::NEW->value,
    ]);
});

it('fails to cancel an order if it is already in_transit (for manager)', function () {
    $this->order->update(['status' => OrderStatus::IN_TRANSIT->value]);

    $response = actingAs($this->manager)
        ->put("/orders/{$this->order->order_number}/status", [
            'status' => OrderStatus::CANCELED->value,
        ]);

    $response->assertSessionHasErrors('status');

    // Status should remain IN_TRANSIT
    $this->assertDatabaseHas('orders', [
        'id' => $this->order->id,
        'status' => OrderStatus::IN_TRANSIT->value,
    ]);
});

it('allows admin to change order status arbitrarily subject to model transitions', function () {
    $response = actingAs($this->admin)
        ->put("/orders/{$this->order->order_number}/status", [
            'status' => OrderStatus::PENDING->value,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('orders', [
        'id' => $this->order->id,
        'status' => OrderStatus::PENDING->value,
    ]);
});

it('fails for admin if transition is invalid in the model', function () {
    $this->order->update(['status' => OrderStatus::DELIVERED->value]);

    // Can't go from DELIVERED -> NEW
    $response = actingAs($this->admin)
        ->put("/orders/{$this->order->order_number}/status", [
            'status' => OrderStatus::NEW->value,
        ]);

    $response->assertSessionHasErrors('status');

    $this->assertDatabaseHas('orders', [
        'id' => $this->order->id,
        'status' => OrderStatus::DELIVERED->value,
    ]);
});
