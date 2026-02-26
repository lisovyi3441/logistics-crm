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
            'created_at' => $this->created_at, // Можна відформатувати тут ->format('d.m.Y H:i')
            'total_price_cents' => $this->total_price_cents,

            'total_price_cents' => $this->total_price_cents,

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
                'allowed_transitions' => collect($this->status->allowedTransitions())->map(fn ($st) => [
                    'value' => $st->value,
                    'label' => $st->label(),
                ]),
            ],

            'can' => [
                'updateStatus' => $request->user()?->can('updateStatus', $this->resource) ?? false,
            ],

            'items' => $this->whenLoaded('items', function () {
                return $this->items->map(fn ($item) => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'weight_kg' => $item->weight_kg,
                    'price_cents' => $item->price_cents,
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
        ];
    }
}
