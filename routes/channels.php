<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('orders.{orderId}', function ($user, $orderId) {
    // Basic logic: User can join if they are super_admin or belong to the same company as order
    $order = \App\Models\Order::find($orderId);

    if (! $order) {
        return false;
    }

    return $user->hasRole('super_admin') || (int) $user->company_id === (int) $order->company_id;
});

Broadcast::channel('companies.{companyId}', function ($user, $companyId) {
    return $user->hasRole('super_admin') || (int) $user->company_id === (int) $companyId;
});
