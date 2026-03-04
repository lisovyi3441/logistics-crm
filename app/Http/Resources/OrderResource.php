<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'created_at' => $this->created_at->format('M d, Y'),
            'total_price_cents' => $this->total_price_cents,
            'currency' => $this->currency,

            // Detailed Pricing Breakdown
            'base_price_cents' => $this->base_price_cents,
            'insurance_fee_cents' => $this->insurance_fee_cents,
            'surcharge_cents' => $this->surcharge_cents,
            'discount_cents' => $this->discount_cents,
            'tax_cents' => $this->tax_cents,

            // Geospatial Routing Data
            'pickup_address' => $this->pickup_address,
            'delivery_address' => $this->delivery_address,
            'distance_km' => (float) $this->distance_km,
            'transit_time_minutes' => (int) $this->transit_time_minutes,

            'truck' => $this->whenLoaded('truck', function () {
                return [
                    'id' => $this->truck->id,
                    'name' => $this->truck->name,
                ];
            }),

            'vehicle_type' => $this->whenLoaded('vehicleType', function () {
                return [
                    'id' => $this->vehicleType->id,
                    'name' => $this->vehicleType->name,
                ];
            }),

            'company' => [
                'name' => $this->company->name ?? 'N/A',
            ],
            'user' => [
                'name' => $this->user->name ?? 'N/A',
            ],

            'status' => [
                'label' => $this->status->label(),
                'color' => $this->status->color(),
                'value' => $this->status->value,
                'allowed_transitions' => array_map(
                    fn ($st) => ['value' => $st->value, 'label' => $st->label()],
                    array_values(array_filter($this->status->allowedTransitions(), function ($st) {
                        $user = auth()->user();
                        if ($user && ! $user->can(\App\Enums\Permissions::UPDATE_ORDER_STATUS->value)) {
                            return $st->value === 'canceled' &&
                                   $user->can(\App\Enums\Permissions::CANCEL_ORDERS->value) &&
                                   in_array($this->status->value, ['new', 'pending'], true);
                        }

                        return true;
                    }))
                ),
            ],

            'can' => [
                'updateStatus' => $request->user()?->can('updateStatus', $this->resource) ?? false,
                'generateCmr' => $request->user()?->can('generateCmr', $this->resource) ?? false,
                'generateInvoice' => $request->user()?->can('generateInvoice', $this->resource) ?? false,
            ],

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'weight_kg' => $item->weight_kg,
                    'cbm' => $item->cbm,
                    'length_cm' => $item->length_cm,
                    'width_cm' => $item->width_cm,
                    'height_cm' => $item->height_cm,
                    'is_dangerous' => $item->is_dangerous,
                    'declared_value_cents' => $item->declared_value_cents,
                ]);
            }),

            'status_histories' => $this->whenLoaded('statusHistories', function () {
                return $this->statusHistories->map(fn ($history) => [
                    'id' => $history->id,
                    'old_status' => $history->old_status?->label(),
                    'new_status' => $history->new_status->label(),
                    'new_status_color' => $history->new_status->color(),
                    'comment' => $history->comment,
                    'created_at' => $history->created_at->format('M d, Y H:i'),
                    'user' => [
                        'name' => $history->user->name ?? 'System',
                    ],
                ]);
            }),

            'documents' => $this->whenLoaded('documents', function () {
                return $this->documents->map(fn ($doc) => [
                    'id' => $doc->id,
                    'type' => $doc->document_type,
                    'created_at' => $doc->created_at->format('M d, Y H:i'),
                    'url_download' => route('orders.documents.download', ['order' => $this->order_number, 'document' => $doc->id, 'action' => 'download']),
                    'url_view' => route('orders.documents.download', ['order' => $this->order_number, 'document' => $doc->id, 'action' => 'view']),
                ]);
            }),
        ];
    }
}
