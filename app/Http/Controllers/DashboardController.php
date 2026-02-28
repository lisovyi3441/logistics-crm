<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    protected DashboardService $dashboardService;

    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    public function index(): Response
    {
        $user = auth()->user();

        return Inertia::render('Dashboard', [
            'stats' => $this->dashboardService->getStats($user),
            'recentOrders' => collect($this->dashboardService->getRecentOrders(10, $user))
                ->map(fn ($order) => array_merge($order, [
                    'url' => route('orders.show', $order['order_number']),
                ])),
        ]);
    }
}
