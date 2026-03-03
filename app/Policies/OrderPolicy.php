<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\Permissions;
use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can(Permissions::VIEW_ALL_ORDERS->value) || 
               $user->can(Permissions::VIEW_COMPANY_ORDERS->value);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        // Admins can view any (handled by Gate::before)
        return $user->company_id === $order->company_id && 
               $user->can(Permissions::VIEW_COMPANY_ORDERS->value);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can(Permissions::CREATE_ORDERS->value) && $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the model (general).
     */
    public function update(User $user, Order $order): bool
    {
        return $user->can(Permissions::EDIT_ORDERS->value) && 
               $user->company_id === $order->company_id;
    }

    /**
     * Custom ability to update order status.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->can(Permissions::UPDATE_ORDER_STATUS->value) || 
               ($user->can(Permissions::CANCEL_ORDERS->value) && $user->company_id === $order->company_id);
    }

    /**
     * Custom ability to assign a truck to an order.
     */
    public function assignTruck(User $user, Order $order): bool
    {
        return $user->can(Permissions::ASSIGN_TRUCKS->value);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return $user->can(Permissions::DELETE_ORDERS->value);
    }
}
