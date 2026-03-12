<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Order;
use App\Models\OrderDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDocumentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_belongs_to_an_order(): void
    {
        $order = Order::factory()->create();
        $document = OrderDocument::factory()->create(['order_id' => $order->id]);

        $this->assertInstanceOf(Order::class, $document->order);
        $this->assertEquals($order->id, $document->order->id);
    }

    public function test_it_belongs_to_a_user(): void
    {
        $user = User::factory()->create();
        $document = OrderDocument::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $document->user);
        $this->assertEquals($user->id, $document->user->id);
    }
}
