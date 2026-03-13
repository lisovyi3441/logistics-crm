<?php

use App\Enums\OrderStatus;
use App\Models\Company;
use App\Models\Order;
use App\Models\Truck;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Support\Facades\Http;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->company = Company::factory()->create();
    $this->manager = User::factory()->create(['company_id' => $this->company->id]);
    $this->manager->assignRole('manager');

    $this->vehicleType = VehicleType::factory()->create([
        'base_price_per_km_cents' => 100,
    ]);

    $this->truck = Truck::factory()->create([
        'vehicle_type_id' => $this->vehicleType->id,
    ]);

    $this->order = Order::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'status' => OrderStatus::NEW->value,
        'vehicle_type_id' => $this->vehicleType->id,
    ]);
});

it('allows admin to assign a valid, free truck', function () {
    $response = actingAs($this->admin)
        ->put("/orders/{$this->order->order_number}/assign-truck", [
            'truck_id' => $this->truck->id,
        ]);

    $response->assertRedirect();
    $response->assertSessionHasNoErrors();

    $this->assertDatabaseHas('orders', [
        'id' => $this->order->id,
        'truck_id' => $this->truck->id,
    ]);

    $this->assertDatabaseHas('order_status_histories', [
        'order_id' => $this->order->id,
        'comment' => "Assigned truck {$this->truck->name} to the order. Updated manually.",
    ]);
});

it('forbids manager from assigning a truck', function () {
    $response = actingAs($this->manager)
        ->put("/orders/{$this->order->order_number}/assign-truck", [
            'truck_id' => $this->truck->id,
        ]);

    $response->assertForbidden();
});

it('fails validation if the truck is currently busy on an in_transit order', function () {
    // Make the truck busy
    Order::factory()->create([
        'truck_id' => $this->truck->id,
        'status' => OrderStatus::IN_TRANSIT->value,
    ]);

    $response = actingAs($this->admin)
        ->put("/orders/{$this->order->order_number}/assign-truck", [
            'truck_id' => $this->truck->id,
        ]);

    $response->assertSessionHasErrors('truck_id');
    $this->assertDatabaseMissing('orders', [
        'id' => $this->order->id,
        'truck_id' => $this->truck->id,
    ]);
});

it('fails action if the order is already delivered', function () {
    $this->order->update(['status' => OrderStatus::DELIVERED->value]);

    $response = actingAs($this->admin)
        ->put("/orders/{$this->order->order_number}/assign-truck", [
            'truck_id' => $this->truck->id,
        ]);

    $response->assertSessionHasErrors('truck_id');
});

it('fails action if truck vehicle type does not match order requested type', function () {
    $differentType = VehicleType::factory()->create();
    $badTruck = Truck::factory()->create(['vehicle_type_id' => $differentType->id]);

    $response = actingAs($this->admin)
        ->put("/orders/{$this->order->order_number}/assign-truck", [
            'truck_id' => $badTruck->id,
        ]);

    $response->assertSessionHasErrors('truck_id');
});

it('calculates price dynamically if order had no vehicle type', function () {
    // Ensure routing fake
    Http::fake([
        'router.project-osrm.org/*' => Http::response([
            'routes' => [
                ['distance' => 50000, 'duration' => 3600, 'geometry' => ''],
            ],
        ], 200),
    ]);

    $orderNoType = Order::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'status' => OrderStatus::NEW->value,
        'vehicle_type_id' => null,
        'distance_km' => 50,
    ]);

    // Create an item to give it some weight/price base
    $orderNoType->items()->create([
        'name' => 'Box',
        'quantity' => 1,
        'weight_kg' => 10,
        'cbm' => 0.5,
        'declared_value_cents' => 1000,
    ]);

    $oldPrice = $orderNoType->total_price_cents;

    $response = actingAs($this->admin)
        ->put("/orders/{$orderNoType->order_number}/assign-truck", [
            'truck_id' => $this->truck->id,
        ]);

    $response->assertSessionHasNoErrors();

    $orderNoType->refresh();

    // We don't check the exact math here (PricingPipeline test does that),
    // but the price should have changed because it picked up the new truck's vehicle_type_id rate.

    $this->assertDatabaseHas('orders', [
        'id' => $orderNoType->id,
        'truck_id' => $this->truck->id,
    ]);

    $this->assertNotEquals($oldPrice, $orderNoType->total_price_cents);
});
