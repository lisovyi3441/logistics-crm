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
            $totalWeight = collect($validatedData['items'])->sum(fn($i) => $i['weight_kg'] * $i['quantity']);
            $totalCbm = collect($validatedData['items'])->sum(fn($i) => ($i['cbm'] ?? 0) * $i['quantity']);
            $isDangerous = collect($validatedData['items'])->contains(fn($i) => $i['is_dangerous'] ?? false);
            
            $totalDeclaredValueCents = collect($validatedData['items'])->sum(function ($item) {
                return $item['quantity'] * ($item['declared_value_cents'] ?? 0);
            });

            $pricingData = new \App\Logistics\Cargo\PricingData(
                weightKg: (float) $totalWeight,
                cbm: (float) $totalCbm,
                isDangerous: (bool) $isDangerous,
                declaredValueCents: (float) $totalDeclaredValueCents,
            );
            $pipeline = new \App\Logistics\Cargo\PricingPipeline();
            $pipelineResult = $pipeline->calculate($pricingData);

            // Fetch calculated price from Pipeline
            $totalPriceCents = (int) $pipelineResult->finalPriceCents;

            $order = Order::create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'truck_id' => $validatedData['truck_id'] ?? null,
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'status' => 'new',
                'total_price_cents' => $totalPriceCents,
                'currency' => 'USD', // Keeping USD as default
                'notes' => $validatedData['notes'] ?? null,
            ]);

            foreach ($validatedData['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'weight_kg' => $item['weight_kg'],
                    'cbm' => $item['cbm'] ?? null,
                    'length_cm' => $item['length_cm'] ?? null,
                    'width_cm' => $item['width_cm'] ?? null,
                    'height_cm' => $item['height_cm'] ?? null,
                    'is_dangerous' => $item['is_dangerous'] ?? false,
                    'declared_value_cents' => $item['declared_value_cents'] ?? 0,
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
