<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Orders\AssignTruckAction;
use App\Actions\Orders\ChangeOrderStatusAction;
use App\Actions\Orders\CreateOrderAction;
use App\Enums\Permissions;
use App\Http\Requests\AssignTruckRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Company;
use App\Models\Order;
use App\Models\Truck;
use App\Models\VehicleType;
use Illuminate\Http\RedirectResponse;
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
        // Manager & Observer: all orders in their company
        if ($user && ! $user->can(Permissions::VIEW_ALL_ORDERS->value)) {
            $query->where('company_id', $user->company_id);
        }

        $orders = $query->paginate(10);

        return Inertia::render('orders/Index', [
            'orders' => OrderResource::collection($orders),
            'can_create' => $user && $user->can('create', Order::class),
        ]);
    }

    public function create(): Response
    {
        $this->authorize('create', Order::class);
        $user = auth()->user();
        $companies = [];

        if ($user->can(Permissions::VIEW_COMPANIES->value)) {
            $companies = Company::select('id', 'name')->get();
        }

        $vehicleTypes = VehicleType::get(['id', 'name', 'max_weight_kg', 'max_volume_cbm']);

        return Inertia::render('orders/Form', [
            'companies' => $companies,
            'vehicleTypes' => $vehicleTypes,
            'is_admin' => $user->can(Permissions::VIEW_ALL_ORDERS->value),
            'default_company_id' => $user->company_id,
        ]);
    }

    public function store(StoreOrderRequest $request, CreateOrderAction $action): RedirectResponse
    {
        $this->authorize('create', Order::class);
        $user = auth()->user();
        $validated = $request->validated();

        $companyId = $user->can(Permissions::VIEW_COMPANIES->value) ? $validated['company_id'] : $user->company_id;

        $action->execute($user, $validated, $companyId);

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);
        $user = auth()->user();

        $order->load(['company', 'user', 'items', 'statusHistories.user', 'truck', 'vehicleType', 'documents']);

        $trucks = [];
        if ($user->can(Permissions::ASSIGN_TRUCKS->value)) {
            $trucks = Truck::get(['id', 'name']);
        }

        return Inertia::render('orders/Show', [
            'order' => new OrderResource($order),
            'trucks' => $trucks,
            'is_admin' => $user->can(Permissions::VIEW_ALL_ORDERS->value),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order, ChangeOrderStatusAction $action): RedirectResponse
    {
        $this->authorize('updateStatus', $order);
        $validated = $request->validated();

        $action->execute($order, $validated['status']);

        return back();
    }

    public function assignTruck(AssignTruckRequest $request, Order $order, AssignTruckAction $action): RedirectResponse
    {
        $this->authorize('assignTruck', $order);
        $validated = $request->validated();

        $action->execute($order, (int) $validated['truck_id']);

        return back()->with('success', 'Truck assigned successfully.');
    }
}
