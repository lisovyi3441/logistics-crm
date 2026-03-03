<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Scaffold all roles and permissions dynamically
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->company = Company::factory()->create();
    $this->manager = User::factory()->create(['company_id' => $this->company->id]);
    $this->manager->assignRole('manager');
});

it('allows admin to create order for any company', function () {
    $targetCompany = Company::factory()->create();

    $response = actingAs($this->admin)
        ->post('/orders', [
            'company_id' => $targetCompany->id,
            'notes' => 'Admin order',
            'pickup_address' => 'Kyiv',
            'pickup_lat' => 50.45,
            'pickup_lng' => 30.52,
            'delivery_address' => 'Lviv',
            'delivery_lat' => 49.83,
            'delivery_lng' => 24.02,
            'items' => [
                [
                    'name' => 'Item 1',
                    'quantity' => 2,
                    'weight_kg' => 10.5,
                    'declared_value_cents' => 5000,
                ],
            ],
        ]);

    $response->assertRedirect(route('orders.index'));

    $this->assertDatabaseHas('orders', [
        'company_id' => $targetCompany->id,
        'user_id' => $this->admin->id,
        'notes' => 'Admin order',
    ]);

    $this->assertDatabaseHas('order_items', [
        'name' => 'Item 1',
        'quantity' => 2,
    ]);

    $this->assertDatabaseHas('order_status_histories', [
        'new_status' => 'new',
        'user_id' => $this->admin->id,
    ]);
});

it('forces manager to create order for their own company', function () {
    $otherCompany = Company::factory()->create();

    $response = actingAs($this->manager)
        ->post('/orders', [
            'company_id' => $otherCompany->id, // Should be ignored or overridden
            'pickup_address' => 'Kyiv',
            'pickup_lat' => 50.45,
            'pickup_lng' => 30.52,
            'delivery_address' => 'Lviv',
            'delivery_lat' => 49.83,
            'delivery_lng' => 24.02,
            'items' => [
                [
                    'name' => 'Manager Item',
                    'quantity' => 1,
                    'weight_kg' => 100,
                    'declared_value_cents' => 10000,
                ],
            ],
        ]);

    $response->assertRedirect(route('orders.index'));

    // Check that it was created for $this->company, not $otherCompany
    $this->assertDatabaseHas('orders', [
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
    ]);

    $this->assertDatabaseMissing('orders', [
        'company_id' => $otherCompany->id,
    ]);
});

it('validates that items array is required and not empty', function () {
    actingAs($this->admin)
        ->post('/orders', [
            'company_id' => $this->company->id,
            'items' => [],
        ])
        ->assertSessionHasErrors(['items']);
});

it('validates each item fields', function () {
    actingAs($this->admin)
        ->post('/orders', [
            'company_id' => $this->company->id,
            'items' => [
                [
                    'name' => '', // Empty name
                    'quantity' => 0, // Invalid quantity
                    'weight_kg' => -1, // Invalid weight
                    'declared_value_cents' => -10, // Invalid price
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'items.0.name',
            'items.0.quantity',
            'items.0.weight_kg',
            'items.0.declared_value_cents',
        ]);
});

it('calculates total price correctly', function () {
    Http::fake([
        'router.project-osrm.org/*' => Http::response([
            'routes' => [
                ['distance' => 50000, 'duration' => 3600, 'geometry' => ''],
            ],
        ], 200),
    ]);

    actingAs($this->admin)
        ->post('/orders', [
            'company_id' => $this->company->id,
            'pickup_address' => 'Kyiv',
            'pickup_lat' => 50.45,
            'pickup_lng' => 30.52,
            'delivery_address' => 'Lviv',
            'delivery_lat' => 49.83,
            'delivery_lng' => 24.02,
            'items' => [
                ['name' => 'A', 'quantity' => 2, 'weight_kg' => 1, 'declared_value_cents' => 1000], // 2000
                ['name' => 'B', 'quantity' => 1, 'weight_kg' => 1, 'declared_value_cents' => 500],  // 500
            ],
        ]);

    // Distance 50km * 100cents = 5000 base. Insurance 1% of 2500 = 25. Subtotal = 5025.
    // Default Tax = 20% of 5025 = 1005. Total = 6030 cents.
    $this->assertDatabaseHas('orders', [
        'total_price_cents' => 6030,
    ]);
});

it('allows managers to update order status for their company', function () {
    $order = Order::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $this->manager->id,
        'status' => 'new',
    ]);

    $response = actingAs($this->manager)
        ->put("/orders/{$order->order_number}/status", [
            'status' => 'canceled',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'canceled',
    ]);

    $this->assertDatabaseHas('order_status_histories', [
        'order_id' => $order->id,
        'new_status' => 'canceled',
        'user_id' => $this->manager->id,
    ]);
});

it('forbids customers from updating order status', function () {
    $observer = User::factory()->create(['company_id' => $this->company->id]);
    $observer->assignRole('observer');

    $order = Order::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $observer->id,
        'status' => 'new',
    ]);

    $response = actingAs($observer)
        ->put("/orders/{$order->order_number}/status", [
            'status' => 'pending',
        ]);

    $response->assertForbidden(); // 403

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'new', // Status should not change
    ]);
});

it('scopes order index correctly for customers', function () {
    $observer1 = User::factory()->create(['company_id' => $this->company->id]);
    $observer1->assignRole('observer');

    $observer2 = User::factory()->create(['company_id' => $this->company->id]);
    $observer2->assignRole('observer');

    Order::factory()->create(['user_id' => $observer1->id, 'company_id' => $this->company->id]);
    Order::factory()->create(['user_id' => $observer2->id, 'company_id' => $this->company->id]);

    // Observer 1 should see all 2 orders in their company
    actingAs($observer1)
        ->get('/orders')
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 2)
        );
});

it('scopes order index correctly for managers', function () {
    $observer1 = User::factory()->create(['company_id' => $this->company->id]);
    $observer1->assignRole('observer');

    $observer2 = User::factory()->create(['company_id' => $this->company->id]);
    $observer2->assignRole('observer');

    Order::factory()->create(['user_id' => $observer1->id, 'company_id' => $this->company->id]);
    Order::factory()->create(['user_id' => $observer2->id, 'company_id' => $this->company->id]);

    // Manager should see 2 orders
    actingAs($this->manager)
        ->get('/orders')
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 2)
        );
});
