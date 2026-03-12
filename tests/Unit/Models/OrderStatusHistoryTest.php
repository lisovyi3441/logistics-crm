<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_an_order(): void
    {
        $order = Order::factory()->create();
        $history = OrderStatusHistory::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $history->order);
    }

    public function test_it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $history = OrderStatusHistory::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $history->user);
    }

    public function test_it_casts_statuses_to_enum(): void
    {
        $history = OrderStatusHistory::factory()->create([
            'old_status' => 'new',
            'new_status' => 'pending',
        ]);

        $this->assertInstanceOf(OrderStatus::class, $history->old_status);
        $this->assertInstanceOf(OrderStatus::class, $history->new_status);
        $this->assertEquals(OrderStatus::NEW, $history->old_status);
        $this->assertEquals(OrderStatus::PENDING, $history->new_status);
    }
}
