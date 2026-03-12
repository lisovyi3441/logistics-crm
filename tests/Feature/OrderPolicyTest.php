<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $manager;

    private User $observer;

    private Company $company;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->company = Company::factory()->create();

        $this->admin = User::factory()->create();
        $this->admin->assignRole('admin');

        $this->manager = User::factory()->create(['company_id' => $this->company->id]);
        $this->manager->assignRole('manager');

        $this->observer = User::factory()->create(['company_id' => $this->company->id]);
        $this->observer->assignRole('observer');
    }

    public function test_admin_can_view_any_order(): void
    {
        $this->assertTrue($this->admin->can('viewAny', Order::class));
    }

    public function test_manager_can_view_any_order(): void
    {
        $this->assertTrue($this->manager->can('viewAny', Order::class));
    }

    public function test_observer_can_view_any_order(): void
    {
        $this->assertTrue($this->observer->can('viewAny', Order::class));
    }

    public function test_user_can_view_order_in_their_company(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);

        $this->assertTrue($this->manager->can('view', $order));
        $this->assertTrue($this->observer->can('view', $order));
    }

    public function test_user_cannot_view_order_in_another_company(): void
    {
        $otherCompany = Company::factory()->create();
        $order = Order::factory()->create(['company_id' => $otherCompany->id]);

        $this->assertFalse($this->manager->can('view', $order));
        $this->assertFalse($this->observer->can('view', $order));
    }

    public function test_manager_can_create_order(): void
    {
        $this->assertTrue($this->manager->can('create', Order::class));
    }

    public function test_observer_cannot_create_order(): void
    {
        $this->assertFalse($this->observer->can('create', Order::class));
    }

    public function test_manager_can_update_order_in_their_company(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);
        $this->assertTrue($this->manager->can('update', $order));
    }

    public function test_manager_cannot_update_order_in_another_company(): void
    {
        $otherCompany = Company::factory()->create();
        $order = Order::factory()->create(['company_id' => $otherCompany->id]);
        $this->assertFalse($this->manager->can('update', $order));
    }

    public function test_manager_can_update_status(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);
        $this->assertTrue($this->manager->can('updateStatus', $order));
    }

    public function test_observer_cannot_update_status_in_their_company(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);
        $this->assertFalse($this->observer->can('updateStatus', $order));
    }

    public function test_observer_cannot_update_status_in_another_company(): void
    {
        $otherCompany = Company::factory()->create();
        $order = Order::factory()->create(['company_id' => $otherCompany->id]);
        $this->assertFalse($this->observer->can('updateStatus', $order));
    }

    public function test_admin_can_assign_truck(): void
    {
        $order = Order::factory()->create();
        $this->assertTrue($this->admin->can('assignTruck', $order));
    }

    public function test_manager_cannot_assign_truck(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);
        $this->assertFalse($this->manager->can('assignTruck', $order));
    }

    public function test_admin_can_delete_order(): void
    {
        $order = Order::factory()->create();
        $this->assertTrue($this->admin->can('delete', $order));
    }

    public function test_manager_cannot_delete_order(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);
        $this->assertFalse($this->manager->can('delete', $order));
    }

    public function test_admin_can_generate_cmr(): void
    {
        $order = Order::factory()->create();
        $this->assertTrue($this->admin->can('generateCmr', $order));
    }

    public function test_manager_cannot_generate_cmr(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);
        $this->assertFalse($this->manager->can('generateCmr', $order));
    }

    public function test_manager_can_generate_invoice_for_their_company(): void
    {
        $order = Order::factory()->create(['company_id' => $this->company->id]);
        $this->assertTrue($this->manager->can('generateInvoice', $order));
    }

    public function test_manager_cannot_generate_invoice_for_another_company(): void
    {
        $otherCompany = Company::factory()->create();
        $order = Order::factory()->create(['company_id' => $otherCompany->id]);
        $this->assertFalse($this->manager->can('generateInvoice', $order));
    }
}
