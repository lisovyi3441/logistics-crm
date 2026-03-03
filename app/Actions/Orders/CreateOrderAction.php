<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Logistics\Cargo\PricingData;
use App\Logistics\Cargo\PricingPipeline;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Services\Routing\RoutingServiceInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateOrderAction
{
    public function __construct(
        private readonly RoutingServiceInterface $routingService
    ) {}

    public function execute(User $user, array $validatedData, ?int $companyIdOverride = null): Order
    {
        $companyId = $companyIdOverride ?? $user->company_id;

        return DB::transaction(function () use ($validatedData, $user, $companyId) {
            $totalWeight = collect($validatedData['items'])->sum(fn ($i) => $i['weight_kg'] * $i['quantity']);
            $totalCbm = collect($validatedData['items'])->sum(fn ($i) => ($i['cbm'] ?? 0) * $i['quantity']);
            $isDangerous = collect($validatedData['items'])->contains(fn ($i) => $i['is_dangerous'] ?? false);

            $totalDeclaredValueCents = collect($validatedData['items'])->sum(function ($item) {
                return $item['quantity'] * ($item['declared_value_cents'] ?? 0);
            });

            $routeData = ['distance_km' => 1, 'duration_minutes' => 60]; // Defaults
            if (! empty($validatedData['pickup_lat']) && ! empty($validatedData['pickup_lng']) &&
                ! empty($validatedData['delivery_lat']) && ! empty($validatedData['delivery_lng'])) {

                $routeData = $this->routingService->getRoute(
                    (float) $validatedData['pickup_lat'],
                    (float) $validatedData['pickup_lng'],
                    (float) $validatedData['delivery_lat'],
                    (float) $validatedData['delivery_lng']
                );
            }

            $pricingData = new PricingData(
                weightKg: (float) $totalWeight,
                cbm: (float) $totalCbm,
                isDangerous: (bool) $isDangerous,
                distanceKm: (float) $routeData['distance_km'],
                declaredValueCents: (float) $totalDeclaredValueCents,
            );
            $pipeline = new PricingPipeline;
            $pipelineResult = $pipeline->calculate($pricingData);

            $totalPriceCents = (int) $pipelineResult->finalPriceCents;

            $order = Order::create([
                'user_id' => $user->id,
                'company_id' => $companyId,
                'vehicle_type_id' => $validatedData['vehicle_type_id'] ?? null,
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'status' => 'new',

                // Pricing Breakdown
                'total_price_cents' => $totalPriceCents,
                'base_price_cents' => (int) $pipelineResult->basePriceCents,
                'insurance_fee_cents' => (int) $pipelineResult->insuranceFeeCents,
                'surcharge_cents' => (int) $pipelineResult->surchargeCents,
                'discount_cents' => (int) $pipelineResult->discountCents,
                'tax_cents' => (int) $pipelineResult->taxCents,

                'currency' => 'UAH',
                'notes' => $validatedData['notes'] ?? null,

                // Geospatial / Routing Data
                'pickup_address' => $validatedData['pickup_address'] ?? null,
                'pickup_lat' => $validatedData['pickup_lat'] ?? null,
                'pickup_lng' => $validatedData['pickup_lng'] ?? null,
                'delivery_address' => $validatedData['delivery_address'] ?? null,
                'delivery_lat' => $validatedData['delivery_lat'] ?? null,
                'delivery_lng' => $validatedData['delivery_lng'] ?? null,
                'distance_km' => $routeData['distance_km'],
                'transit_time_minutes' => $routeData['duration_minutes'],
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
