<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    /**
     * Perform pre-authorization checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('manager') || $user->hasRole('customer');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Order $order): bool
    {
        if ($user->hasRole('customer')) {
            return $user->id === $order->user_id;
        }

        return $user->company_id === $order->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Must belong to a company to create an order (unless admin, handled in before())
        return $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the model (general).
     */
    public function update(User $user, Order $order): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the order's status.
     * Only admins and managers can change the state of an order.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return $user->hasRole('manager') && $user->company_id === $order->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Order $order): bool
    {
        return false;
    }
}
