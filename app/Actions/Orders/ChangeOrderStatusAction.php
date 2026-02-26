<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Validation\ValidationException;

class ChangeOrderStatusAction
{
    public function execute(Order $order, string $newStatus): Order
    {
        // Ensure the new status is a valid enum value
        $statusEnum = OrderStatus::tryFrom($newStatus);

        if (! $statusEnum) {
            throw ValidationException::withMessages([
                'status' => ['Invalid order status.'],
            ]);
        }

        // If status is the same, do nothing
        if ($order->status === $statusEnum) {
            return $order;
        }

        if ($order->status && ! $order->status->canTransitionTo($statusEnum)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'status' => ["Cannot transition from {$order->status->label()} to {$statusEnum->label()}."],
            ]);
        }

        $oldStatus = $order->status;
        $order->status = $statusEnum;
        $order->save();

        // Log the history
        $order->statusHistories()->create([
            'user_id' => auth()->id(),
            'old_status' => $oldStatus?->value,
            'new_status' => $statusEnum->value,
            'comment' => 'Status manually changed via system.',
        ]);

        return $order;
    }
}
