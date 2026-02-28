<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    Role::firstOrCreate(['name' => 'admin']);
    Role::firstOrCreate(['name' => 'manager']);
    Role::firstOrCreate(['name' => 'customer']);

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
            'items' => [
                [
                    'name' => 'Item 1',
                    'quantity' => 2,
                    'weight_kg' => 10.5,
                    'price_cents' => 5000,
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
            'items' => [
                [
                    'name' => 'Manager Item',
                    'quantity' => 1,
                    'weight_kg' => 100,
                    'price_cents' => 10000,
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
                    'price_cents' => -10, // Invalid price
                ],
            ],
        ])
        ->assertSessionHasErrors([
            'items.0.name',
            'items.0.quantity',
            'items.0.weight_kg',
            'items.0.price_cents',
        ]);
});

it('calculates total price correctly', function () {
    actingAs($this->admin)
        ->post('/orders', [
            'company_id' => $this->company->id,
            'items' => [
                ['name' => 'A', 'quantity' => 2, 'weight_kg' => 1, 'price_cents' => 1000], // 2000
                ['name' => 'B', 'quantity' => 1, 'weight_kg' => 1, 'price_cents' => 500],  // 500
            ],
        ]);

    $this->assertDatabaseHas('orders', [
        'total_price_cents' => 1845,
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
            'status' => 'pending',
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('orders', [
        'id' => $order->id,
        'status' => 'pending',
    ]);

    $this->assertDatabaseHas('order_status_histories', [
        'order_id' => $order->id,
        'new_status' => 'pending',
        'user_id' => $this->manager->id,
    ]);
});

it('forbids customers from updating order status', function () {
    $customer = User::factory()->create(['company_id' => $this->company->id]);
    $customer->assignRole('customer');

    $order = Order::factory()->create([
        'company_id' => $this->company->id,
        'user_id' => $customer->id,
        'status' => 'new',
    ]);

    $response = actingAs($customer)
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
    $customer1 = User::factory()->create(['company_id' => $this->company->id]);
    $customer1->assignRole('customer');

    $customer2 = User::factory()->create(['company_id' => $this->company->id]);
    $customer2->assignRole('customer');

    Order::factory()->create(['user_id' => $customer1->id, 'company_id' => $this->company->id]);
    Order::factory()->create(['user_id' => $customer2->id, 'company_id' => $this->company->id]);

    // Customer 1 should only see 1 order
    actingAs($customer1)
        ->get('/orders')
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 1)
        );
});

it('scopes order index correctly for managers', function () {
    $customer1 = User::factory()->create(['company_id' => $this->company->id]);
    $customer1->assignRole('customer');

    $customer2 = User::factory()->create(['company_id' => $this->company->id]);
    $customer2->assignRole('customer');

    Order::factory()->create(['user_id' => $customer1->id, 'company_id' => $this->company->id]);
    Order::factory()->create(['user_id' => $customer2->id, 'company_id' => $this->company->id]);

    // Manager should see 2 orders
    actingAs($this->manager)
        ->get('/orders')
        ->assertInertia(fn ($page) => $page
            ->has('orders.data', 2)
        );
});
