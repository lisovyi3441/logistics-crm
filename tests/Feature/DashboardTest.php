<?php

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use App\Services\DashboardService;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleSeeder::class);

    $this->admin = User::factory()->create();
    $this->admin->assignRole('admin');

    $this->companyA = Company::factory()->create(['name' => 'Company A']);
    $this->managerA = User::factory()->create(['company_id' => $this->companyA->id]);
    $this->managerA->assignRole('manager');

    $this->companyB = Company::factory()->create(['name' => 'Company B']);

    // Create orders for Company A
    Order::factory()->count(3)->create([
        'company_id' => $this->companyA->id,
        'total_price_cents' => 10000, // $100
        'status' => 'delivered',
    ]);

    // Create an order for Company B
    Order::factory()->create([
        'company_id' => $this->companyB->id,
        'total_price_cents' => 50000, // $500
        'status' => 'delivered',
    ]);
});

test('guests are redirected to the login page', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('admin sees global statistics and all recent orders', function () {
    actingAs($this->admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.totalOrders', 4)
            ->where('stats.activeCompanies', 2)
            ->where('stats.totalRevenue', '800.00') // 100*3 + 500
            ->has('recentOrders', 4)
        );
});

test('manager sees only their company statistics', function () {
    actingAs($this->managerA)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->where('stats.totalOrders', 3)
            ->where('stats.activeCompanies', 1)
            ->where('stats.totalRevenue', '300.00') // 100*3
            ->has('recentOrders', 3)
            ->where('recentOrders.0.company_name', 'Company A')
        );
});

test('dashboard service internal methods honor user scoping', function () {
    $service = new DashboardService;

    // Admin context
    $adminStats = $service->getStats($this->admin);
    expect($adminStats['totalOrders'])->toBe(4);

    // Manager context
    $managerStats = $service->getStats($this->managerA);
    expect($managerStats['totalOrders'])->toBe(3)
        ->and($managerStats['activeCompanies'])->toBe(1);
});
