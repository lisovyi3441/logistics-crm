<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Permissions;
use App\Http\Resources\OrderResource;
use App\Models\Company;
use App\Models\Order;
use App\Models\User;

class DashboardService
{
    /**
     * Get aggregated statistics for the dashboard.
     *
     * @return array<string, mixed>
     */
    public function getStats(?User $user = null): array
    {
        // Use our local scope for consistent access logic
        $orderQuery = Order::forUser($user);

        $activeCompanies = ($user && ! $user->can(Permissions::VIEW_GLOBAL_DASHBOARD->value)) ? 1 : Company::count();

        $totalRevenueCents = (clone $orderQuery)->where('status', '!=', 'canceled')->sum('total_price_cents');

        return [
            'totalOrders' => $orderQuery->count(),
            'totalRevenue' => number_format((float) $totalRevenueCents / 100, 2, '.', ''),
            'activeCompanies' => $activeCompanies,
        ];
    }

    /**
     * Get recent orders list formatted for dashboard.
     */
    public function getRecentOrders(int $limit = 5, ?User $user = null): array
    {
        $orders = Order::forUser($user)
            ->with(['company', 'user'])
            ->latest()
            ->take($limit)
            ->get();

        // Use OrderResource for standardized response
        return OrderResource::collection($orders)->resolve();
    }
}
