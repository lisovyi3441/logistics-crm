<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole('manager') || $user->hasRole('observer');
    }

    public function view(User $user, Order $order): bool
    {
        return $user->company_id === $order->company_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('manager') && $user->company_id !== null;
    }

    /**
     * Determine whether the user can update the model (general).
     */
    public function update(User $user, Order $order): bool
    {
        return false;
    }

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
