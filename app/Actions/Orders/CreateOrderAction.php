<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function execute(User $user, array $validatedData, ?int $companyIdOverride = null): Order
    {
        $companyId = $companyIdOverride ?? $user->company_id;

        return DB::transaction(function () use ($validatedData, $user, $companyId) {
            $totalPriceCents = collect($validatedData['items'])->sum(function ($item) {
                return $item['quantity'] * $item['price_cents'];
            });

            $order = Order::create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'status' => 'new',
                'total_price_cents' => $totalPriceCents,
                'currency' => 'USD',
                'notes' => $validatedData['notes'] ?? null,
            ]);

            foreach ($validatedData['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'weight_kg' => $item['weight_kg'],
                    'price_cents' => $item['price_cents'],
                ]);
            }

            $order->statusHistories()->create([
                'user_id' => $user->id,
                'old_status' => null,
                'new_status' => 'new',
                'comment' => 'Order created.',
            ]);

            return $order;
        });
    }
}
