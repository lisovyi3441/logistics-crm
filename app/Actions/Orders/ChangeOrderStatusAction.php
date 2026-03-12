<?php

declare(strict_types=1);

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\Permissions;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Action for changing order status with state-machine and permissions checks.
 */
class ChangeOrderStatusAction
{
    /**
     * Changes order status.
     *
     * @param  Order  $order  The order.
     * @param  string  $newStatus  The new status string.
     * @return Order The updated order model.
     *
     * @throws ValidationException If transition is forbidden or insufficient permissions.
     */
    public function execute(Order $order, string $newStatus): Order
    {
        $statusEnum = OrderStatus::from($newStatus);

        return DB::transaction(function () use ($order, $statusEnum) {
            // Acquire pessimistic lock (lockForUpdate) and refresh order data
            // to prevent race conditions.
            Order::where('id', $order->id)->lockForUpdate()->value('id');
            $order->refresh();

            $user = auth()->user();
            if ($user !== null && ! $user->can(Permissions::UPDATE_ORDER_STATUS->value)) {
                // Check if user has at least cancellation permissions
                if ($statusEnum !== OrderStatus::CANCELED || ! $user->can(Permissions::CANCEL_ORDERS->value)) {
                    throw ValidationException::withMessages([
                        'status' => ['You do not have permission to change status to this value.'],
                    ]);
                }
                // Only new or pending orders can be canceled
                if (! in_array($order->status, [OrderStatus::NEW, OrderStatus::PENDING], true)) {
                    throw ValidationException::withMessages([
                        'status' => ['Orders already in progress cannot be canceled.'],
                    ]);
                }
            }

            if ($order->status->value === $statusEnum->value) {
                return $order;
            }

            if (! $order->status->canTransitionTo($statusEnum)) {
                // Check if the transition is allowed by our state-machine
                throw ValidationException::withMessages([
                    'status' => ["Cannot transition from {$order->status->label()} to {$statusEnum->label()}."],
                ]);
            }

            $oldStatus = $order->status;
            $order->status = $statusEnum;
            $order->save();

            return $order;
        });
    }
}
