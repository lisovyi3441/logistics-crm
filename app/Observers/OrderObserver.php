<?php

declare(strict_types=1);

namespace App\Observers;

use App\Events\OrderUpdated;
use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class OrderObserver
{
    /**
     * Handle the Order "updated" event.
     * We use this to automatically track status changes.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            $user = Auth::user();

            $comment = $user
                ? 'Updated manually.'
                : 'Updated by the system.';

            $order->statusHistories()->create([
                'user_id' => Auth::id(),
                'old_status' => $order->getOriginal('status'),
                'new_status' => $order->status,
                'comment' => $comment,
            ]);

            OrderUpdated::dispatch($order, "Order status changed to {$order->status->label()}.");
        }
    }
}
