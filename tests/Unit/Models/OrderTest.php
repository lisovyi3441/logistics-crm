<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\OrderStatus;
use App\Models\Company;
use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Truck;
use App\Models\User;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    public function test_order_belongs_to_company(): void
    {
        $company = Company::factory()->create();
        $order = Order::factory()->create(['company_id' => $company->id]);

        $this->assertInstanceOf(Company::class, $order->company);
        $this->assertEquals($company->id, $order->company->id);
    }

    public function test_order_belongs_to_truck(): void
    {
        $truck = Truck::factory()->create();
        $order = Order::factory()->create(['truck_id' => $truck->id]);

        $this->assertInstanceOf(Truck::class, $order->truck);
        $this->assertEquals($truck->id, $order->truck->id);
    }

    public function test_order_belongs_to_vehicle_type(): void
    {
        $vehicleType = VehicleType::factory()->create();
        $order = Order::factory()->create(['vehicle_type_id' => $vehicleType->id]);

        $this->assertInstanceOf(VehicleType::class, $order->vehicleType);
        $this->assertEquals($vehicleType->id, $order->vehicleType->id);
    }

    public function test_order_has_many_items(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertCount(3, $order->items);
        $this->assertInstanceOf(OrderItem::class, $order->items->first());
    }

    public function test_order_has_many_status_histories(): void
    {
        $order = Order::factory()->create();
        OrderStatusHistory::factory()->count(2)->create(['order_id' => $order->id]);

        $this->assertCount(2, $order->statusHistories);
        $this->assertInstanceOf(OrderStatusHistory::class, $order->statusHistories->first());
    }

    public function test_order_has_many_documents(): void
    {
        $order = Order::factory()->create();
        OrderDocument::factory()->count(2)->create(['order_id' => $order->id]);

        $this->assertCount(2, $order->documents);
        $this->assertInstanceOf(OrderDocument::class, $order->documents->first());
    }

    public function test_order_uses_order_number_as_route_key(): void
    {
        $order = new Order;
        $this->assertEquals('order_number', $order->getRouteKeyName());
    }

    public function test_order_status_is_casted_to_enum(): void
    {
        $order = Order::factory()->create(['status' => 'new']);
        $this->assertInstanceOf(OrderStatus::class, $order->status);
        $this->assertEquals(OrderStatus::NEW, $order->status);
    }
}
