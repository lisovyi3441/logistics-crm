<?php

declare(strict_types=1);

namespace Tests\Unit\Resources;

use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Truck;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Request;
use Tests\TestCase;

class OrderResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_correct_basic_data(): void
    {
        $order = Order::factory()->create([
            'order_number' => 'ORD-123',
            'total_price_cents' => 1000,
        ]);

        $resource = new OrderResource($order);
        $data = $resource->toArray(request());

        $this->assertEquals('ORD-123', $data['order_number']);
        $this->assertEquals(1000, $data['total_price_cents']);
        $this->assertArrayHasKey('status', $data);
        $this->assertArrayHasKey('label', $data['status']);
    }

    public function test_it_includes_loaded_relationships(): void
    {
        $truck = Truck::factory()->create(['name' => 'Volvo FMX']);
        $vehicleType = VehicleType::factory()->create(['name' => 'Tautliner']);
        $order = Order::factory()->create([
            'truck_id' => $truck->id,
            'vehicle_type_id' => $vehicleType->id,
        ]);

        OrderItem::factory()->create(['order_id' => $order->id, 'name' => 'Pallet']);

        // Load relationships AFTER creating all related data
        $order->load(['truck', 'vehicleType', 'items']);

        $resource = new OrderResource($order);
        $data = $resource->resolve(); // Use resolve() to get the actual array

        $this->assertEquals('Volvo FMX', $data['truck']['name']);
        $this->assertEquals('Tautliner', $data['vehicle_type']['name']);
        $this->assertCount(1, $data['items']);
        $this->assertEquals('Pallet', $data['items'][0]['name']);
    }

    public function test_it_handles_missing_relationships_gracefully(): void
    {
        $order = Order::factory()->create([
            'truck_id' => null,
            'vehicle_type_id' => null,
        ]);

        $resource = new OrderResource($order);
        // Using resolve() instead of toArray(request()) to simulate JSON transformation
        $data = $resource->resolve();

        $this->assertArrayNotHasKey('truck', $data);
        $this->assertArrayNotHasKey('vehicle_type', $data);
    }
}
