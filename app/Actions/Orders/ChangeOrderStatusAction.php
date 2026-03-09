<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChangeOrderStatusAction
{
    public function execute(Order $order, string $newStatus): Order
    {
        $statusEnum = OrderStatus::from($newStatus);

        return DB::transaction(function () use ($order, $statusEnum) {
            // Acquire pessimistic lock on the order and refresh data to prevent race conditions
            Order::where('id', $order->id)->lockForUpdate()->value('id');
            $order->refresh();

            $user = auth()->user();
            if ($user !== null && ! $user->can(\App\Enums\Permissions::UPDATE_ORDER_STATUS->value)) {
                // If they can't do arbitrary updates, meaning they can only CANCEL
                if ($statusEnum !== OrderStatus::CANCELED || ! $user->can(\App\Enums\Permissions::CANCEL_ORDERS->value)) {
                    throw ValidationException::withMessages([
                        'status' => ['You are not authorized to change the status to this value.'],
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
        });
    }
}
