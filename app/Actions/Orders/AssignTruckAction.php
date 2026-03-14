<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Events\OrderUpdated;
use App\Logistics\Cargo\PricingData;
use App\Logistics\Cargo\PricingPipeline;
use App\Models\Order;
use App\Models\Truck;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Action for assigning a specific truck to an order.
 */
class AssignTruckAction
{
    /**
     * Executes the truck assignment process.
     *
     * @param  Order  $order  The order being assigned.
     * @param  int  $truckId  The truck ID.
     *
     * @throws ValidationException If status doesn't allow assignment or vehicle types mismatch.
     */
    public function execute(Order $order, int $truckId): void
    {
        DB::transaction(function () use ($order, $truckId) {
            // Lock the order for update to prevent race conditions during concurrent requests
            Order::where('id', $order->id)->lockForUpdate()->value('id');
            $order->refresh();

            // Use .value for safe Enum comparison as seen by static analysis
            $currentStatus = $order->status->value;

            // Check if the current order status allowed truck assignment
            if (in_array($currentStatus, ['in_transit', 'delivered', 'canceled'], true)) {
                throw ValidationException::withMessages([
                    'truck_id' => 'Cannot assign truck to an order that is already in transit, delivered or canceled.',
                ]);
            }

            // Get the truck and lock it for update to prevent multiple assignments in concurrent requests
            $truck = Truck::where('id', $truckId)
                ->select(['id', 'name', 'vehicle_type_id'])
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->vehicle_type_id && $truck->vehicle_type_id !== $order->vehicle_type_id) {
                throw ValidationException::withMessages([
                    'truck_id' => 'Selected truck type does not match the order requested vehicle type.',
                ]);
            }

            $oldPrice = $order->total_price_cents;
            $order->update([
                'truck_id' => $truck->id,
            ]);

            $priceChangedNote = '';
            if (! $order->vehicle_type_id && $truck->vehicle_type_id) {
                // Load items to avoid N+1 and ensure we have data for pricing
                $order->load('items');

                // If no vehicle type was specified, recalculate price based on the selected truck's rate
                $pricingData = new PricingData(
                    weightKg: (float) ($order->items->sum('weight_kg') ?? 0.0),
                    cbm: (float) ($order->items->sum('cbm') ?? 0.0),
                    isDangerous: (bool) $order->items->contains('is_dangerous', true),
                    distanceKm: (float) ($order->distance_km ?? 0.0),
                    declaredValueCents: (float) ($order->items->sum('declared_value_cents') ?? 0.0)
                );
                $pricingData->requestData = ['vehicle_type_id' => $truck->vehicle_type_id];

                $pricingResult = app(PricingPipeline::class)->calculate($pricingData);

                $order->update([
                    'base_price_cents' => (int) round($pricingResult->basePriceCents),
                    'insurance_fee_cents' => (int) round($pricingResult->insuranceFeeCents),
                    'surcharge_cents' => (int) round($pricingResult->surchargeCents),
                    'tax_cents' => (int) round($pricingResult->taxCents),
                    'total_price_cents' => (int) round($pricingResult->finalPriceCents),
                ]);

                // Calculate diff for informative logging
                $diff = $order->total_price_cents - $oldPrice;
                if ($diff !== 0) {
                    $diffFormatted = number_format(abs($diff) / 100, 2, '.', '');
                    $direction = $diff > 0 ? 'increased' : 'decreased';
                    $priceChangedNote = " Price {$direction} by {$diffFormatted} UAH due to selected vehicle type rates.";
                }
            }

            // Note: We still log the truck assignment specifically because it's a unique event
            // but we ensure we don't duplicate general status changes.
            $user = auth()->user();
            $manualNote = $user ? ' Updated manually.' : ' Updated by the system.';

            $order->statusHistories()->create([
                'user_id' => auth()->id(),
                'old_status' => $order->status->value,
                'new_status' => $order->status->value,
                'comment' => "Assigned truck {$truck->name} to the order.{$priceChangedNote}{$manualNote}",
            ]);

            OrderUpdated::dispatch($order, "Truck {$truck->name} assigned to the order.");
        });
    }
}
