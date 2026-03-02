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
        $statusEnum = OrderStatus::tryFrom($newStatus);

        if (! $statusEnum) {
            throw ValidationException::withMessages([
                'status' => ['Invalid order status.'],
            ]);
        }

        $user = auth()->user();
        if ($user !== null && $user->hasRole('manager') && ! $user->hasRole('admin')) {
            if ($statusEnum !== OrderStatus::CANCELED) {
                throw ValidationException::withMessages([
                    'status' => ['Managers can only cancel un-shipped orders.'],
                ]);
            }
            if (! in_array($order->status, [OrderStatus::NEW, OrderStatus::PENDING], true)) {
                throw ValidationException::withMessages([
                    'status' => ['Orders already in progress cannot be canceled.'],
                ]);
            }
        }

        if ($order->status === $statusEnum) {
            return $order;
        }

        if ($order->status && ! $order->status->canTransitionTo($statusEnum)) {
            throw ValidationException::withMessages([
                'status' => ["Cannot transition from {$order->status->label()} to {$statusEnum->label()}."],
            ]);
        }

        $oldStatus = $order->status;
        $order->status = $statusEnum;
        $order->save();

        $order->statusHistories()->create([
            'user_id' => auth()->id(),
            'old_status' => $oldStatus?->value,
            'new_status' => $statusEnum->value,
            'comment' => 'Status manually changed via system.',
        ]);

        return $order;
    }
}
