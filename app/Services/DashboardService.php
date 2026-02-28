<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Company;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Get aggregate statistics for the dashboard.
     *
     * @return array<string, mixed>
     */
    public function getStats(?User $user = null): array
    {
        $orderQuery = Order::query();

        if ($user && ! $user->hasRole('admin')) {
            $orderQuery->where('company_id', $user->company_id);
        }

        // Only count active companies if admin, else it's always 1
        $activeCompanies = ($user && ! $user->hasRole('admin')) ? 1 : Company::count();

        $totalRevenueCents = (clone $orderQuery)->where('status', '!=', 'canceled')->sum('total_price_cents');

        return [
            'totalOrders' => $orderQuery->count(),
            'totalRevenue' => number_format((float) $totalRevenueCents / 100, 2, '.', ''),
            'activeCompanies' => $activeCompanies,
        ];
    }

    /**
     * Get the most recent orders formatted for the dashboard presentation.
     */
    public function getRecentOrders(int $limit = 5, ?User $user = null): Collection
    {
        $query = Order::with('company')->latest();

        if ($user && ! $user->hasRole('admin')) {
            $query->where('company_id', $user->company_id);
        }

        return $query->take($limit)
            ->get()
            ->map(fn (Order $order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'company_name' => $order->company->name ?? 'N/A',
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'status_color' => $order->status->color(),
                'total_price' => number_format((float) $order->total_price_cents / 100, 2, '.', ''),
                'currency' => $order->currency,
                'created_at' => $order->created_at->format('M d, Y'),
            ]);
    }
}
