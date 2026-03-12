<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TruckRequest;
use App\Models\Truck;
use App\Models\VehicleType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TruckController extends Controller
{
    /**
     * Display a listing of the trucks.
     */
    public function index(): Response
    {
        $this->authorize('viewAny', Truck::class);

        $trucks = Truck::with('vehicleType')
            ->withExists('activeOrders')
            ->paginate(10);

        return Inertia::render('trucks/Index', [
            'trucks' => $trucks,
        ]);
    }

    /**
     * Show the form for creating a new truck.
     */
    public function create(): Response
    {
        $this->authorize('create', Truck::class);

        return Inertia::render('trucks/Form', [
            'vehicleTypes' => VehicleType::all(['id', 'name']),
        ]);
    }

    /**
     * Store a newly created truck in storage.
     */
    public function store(TruckRequest $request): RedirectResponse
    {
        $this->authorize('create', Truck::class);

        Truck::create($request->validated());

        return redirect()->route('trucks.index')->with('success', 'Truck added successfully.');
    }

    /**
     * Show the form for editing the specified truck.
     */
    public function edit(Truck $truck): Response|RedirectResponse
    {
        $this->authorize('update', $truck);

        // Do not allow editing truck configuration if it's currently on a trip
        if ($truck->isBusy()) {
            return redirect()->route('trucks.index')->with('error', 'Cannot edit a truck that is currently busy with an active order.');
        }

        return Inertia::render('trucks/Form', [
            'truck' => $truck,
            'vehicleTypes' => VehicleType::all(['id', 'name']),
        ]);
    }

    /**
     * Update the specified truck in storage.
     */
    public function update(TruckRequest $request, Truck $truck): RedirectResponse
    {
        $this->authorize('update', $truck);

        if ($truck->isBusy()) {
            return redirect()->route('trucks.index')->with('error', 'Cannot update a truck that is currently busy with an active order.');
        }

        $truck->update($request->validated());

        return redirect()->route('trucks.index')->with('success', 'Truck updated successfully.');
    }

    /**
     * Remove the specified truck from storage.
     */
    public function destroy(Truck $truck): RedirectResponse
    {
        $this->authorize('delete', $truck);

        if ($truck->isBusy()) {
            return redirect()->route('trucks.index')->with('error', 'Cannot delete a truck that is currently busy with an active order.');
        }

        $truck->delete();

        return redirect()->route('trucks.index')->with('success', 'Truck deleted successfully.');
    }
}
