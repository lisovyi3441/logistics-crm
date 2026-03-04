<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Logistics\Cargo\PricingData;
use App\Logistics\Cargo\PricingPipeline;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssignTruckAction
{
    /**
     * @throws ValidationException
     */
    public function execute(Order $order, int $truckId): void
    {
        if (in_array($order->status, [OrderStatus::IN_TRANSIT, OrderStatus::DELIVERED, OrderStatus::CANCELED], true)) {
            throw ValidationException::withMessages([
                'truck_id' => 'Cannot assign a truck to an order that is already in transit, delivered, or canceled.',
            ]);
        }

        $truck = Truck::find($truckId);

        if ($order->vehicle_type_id && $truck->vehicle_type_id !== $order->vehicle_type_id) {
            throw ValidationException::withMessages([
                'truck_id' => 'The selected physical truck does not match the requested Vehicle Type for this order.',
            ]);
        }

        DB::transaction(function () use ($order, $truck) {
            $oldPrice = $order->total_price_cents;
            $order->update([
                'truck_id' => $truck->id,
            ]);

            $priceChangedNote = '';
            if (! $order->vehicle_type_id && $truck->vehicle_type_id) {
                $pricingData = new PricingData(
                    (float) ($order->items->sum('weight_kg') ?? 0.0),
                    (float) ($order->items->sum('cbm') ?? 0.0),
                    $order->items->contains('is_dangerous', true),
                    (float) ($order->distance_km ?? 0.0),
                    (float) ($order->items->sum('declared_value_cents') ?? 0.0)
                );
                $pricingData->requestData = ['vehicle_type_id' => $truck->vehicle_type_id];

                $pricingResult = app(PricingPipeline::class)->calculate($pricingData);

                $order->update([
                    'base_price_cents' => $pricingResult->basePriceCents,
                    'insurance_fee_cents' => $pricingResult->insuranceFeeCents,
                    'surcharge_cents' => $pricingResult->surchargeCents,
                    'tax_cents' => $pricingResult->taxCents,
                    'total_price_cents' => $pricingResult->finalPriceCents,
                ]);

                $diff = $order->total_price_cents - $oldPrice;
                if ($diff !== 0) {
                    $diffFormatted = number_format(abs($diff) / 100, 2, '.', '');
                    $direction = $diff > 0 ? 'increased' : 'decreased';
                    $priceChangedNote = " Price {$direction} by {$diffFormatted} EUR due to vehicle type tariff.";
                }
            }

            $order->statusHistories()->create([
                'user_id' => auth()->id() ?? 1,
                'old_status' => $order->status,
                'new_status' => $order->status,
                'comment' => "Assigned truck {$truck->name} to the order.{$priceChangedNote}",
            ]);
        });
    }
}
