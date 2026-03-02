<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Truck;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TruckController extends Controller
{
    public function index()
    {
        $trucks = Truck::with('vehicleType')
            ->withExists('activeOrders')
            ->paginate(10);

        return Inertia::render('trucks/Index', [
            'trucks' => $trucks,
        ]);
    }

    public function create()
    {
        return Inertia::render('trucks/Form', [
            'vehicleTypes' => VehicleType::all(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $messages = [
            'license_plate.regex' => 'The license plate format is invalid. Use Ukrainian format (e.g., AA 1234 BB) using either Latin or Cyrillic characters.',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_plate' => ['required', 'string', 'max:20', 'unique:trucks,license_plate', 'regex:/^[ABCEHIKMOPTXАВЕСНКІМОРТХ]{2}\s?\d{4}\s?[ABCEHIKMOPTXАВЕСНКІМОРТХ]{2}$/ui'],
            'vehicle_type_id' => 'required|exists:vehicle_types,id',
        ], $messages);

        Truck::create($validated);

        return redirect()->route('trucks.index')->with('success', 'Truck added successfully.');
    }

    public function edit(Truck $truck)
    {
        if ($truck->activeOrders()->exists()) {
            return redirect()->route('trucks.index')->with('error', 'Cannot edit a truck that is currently busy with an active order.');
        }

        return Inertia::render('trucks/Form', [
            'truck' => $truck,
            'vehicleTypes' => VehicleType::all(['id', 'name']),
        ]);
    }

    public function update(Request $request, Truck $truck)
    {
        if ($truck->activeOrders()->exists()) {
            return redirect()->route('trucks.index')->with('error', 'Cannot update a truck that is currently busy with an active order.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'license_plate' => ['required', 'string', 'max:20', Rule::unique('trucks')->ignore($truck->id), 'regex:/^[ABCEHIKMOPTXАВЕСНКІМОРТХ]{2}\s?\d{4}\s?[ABCEHIKMOPTXАВЕСНКІМОРТХ]{2}$/ui'],
        ], [
            'license_plate.regex' => 'The license plate format is invalid. Use Ukrainian format (e.g., AA 1234 BB) using either Latin or Cyrillic characters.',
        ]);

        $truck->update($validated);

        return redirect()->route('trucks.index')->with('success', 'Truck updated successfully.');
    }

    public function destroy(Truck $truck)
    {
        if ($truck->activeOrders()->exists()) {
            return redirect()->route('trucks.index')->with('error', 'Cannot delete a truck that is currently busy with an active order.');
        }

        $truck->delete();

        return redirect()->route('trucks.index')->with('success', 'Truck deleted successfully.');
    }
}
