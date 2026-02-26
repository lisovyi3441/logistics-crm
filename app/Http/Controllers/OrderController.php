<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Orders\ChangeOrderStatusAction;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Company;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Order::class);
        $user = auth()->user();

        $query = Order::with('company', 'user')->latest();

        // RBAC Scoping:
        // Admin: all orders
        // Manager: all orders in their company
        // Customer: only their own orders
        if ($user->hasRole('manager')) {
            $query->where('company_id', $user->company_id);
        } elseif ($user->hasRole('customer')) {
            $query->where('user_id', $user->id);
        } elseif (! $user->hasRole('admin')) {
            // Safety fallback for other roles if they somehow bypass viewAny
            $query->where('id', 0);
        }

        $orders = $query->paginate(10);

        return Inertia::render('orders/Index', [
            'orders' => OrderResource::collection($orders),
            'can_create' => true, // Simple flag for UI
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Order::class);
        $user = auth()->user();
        $companies = [];

        if ($user->hasRole('admin')) {
            $companies = Company::select('id', 'name')->get();
        }

        return Inertia::render('orders/Form', [
            'companies' => $companies,
            'is_admin' => $user->hasRole('admin'),
            'default_company_id' => $user->company_id,
        ]);
    }

    public function store(StoreOrderRequest $request, \App\Actions\Orders\CreateOrderAction $action): RedirectResponse
    {
        $this->authorize('create', Order::class);
        $user = auth()->user();
        $validated = $request->validated();

        $companyId = $user->hasRole('admin') ? $validated['company_id'] : $user->company_id;

        $action->execute($user, $validated, $companyId);

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);
        $user = auth()->user();

        $order->load(['company', 'user', 'items', 'statusHistories.user']);

        return Inertia::render('orders/Show', [
            'order' => new OrderResource($order),
        ]);
    }

    public function updateStatus(Request $request, Order $order, ChangeOrderStatusAction $action): RedirectResponse
    {
        $this->authorize('updateStatus', $order);
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $action->execute($order, $validated['status']);

        return back();
    }
}
