<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tariff;
use App\Models\VehicleType;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TariffController extends Controller
{
    public function edit()
    {
        abort_if(! auth()->user()->can(\App\Enums\Permissions::EDIT_TARIFFS->value), 403);

        $tariff = Tariff::firstOrCreate([], [
            'price_per_km_cents' => 100,
            'insurance_rate_percent' => 1.0,
            'tax_rate_percent' => 20.0,
            'adr_surcharge_percent' => 25.0,
        ]);

        return Inertia::render('tariffs/Edit', [
            'tariff' => $tariff,
            'vehicleTypes' => VehicleType::all(),
        ]);
    }

    public function update(Request $request)
    {
        abort_if(! auth()->user()->can(\App\Enums\Permissions::EDIT_TARIFFS->value), 403);

        $validated = $request->validate([
            'insurance_rate_percent' => 'required|numeric|min:0|max:100',
            'tax_rate_percent' => 'required|numeric|min:0|max:100',
            'adr_surcharge_percent' => 'required|numeric|min:0|max:100',
            'vehicle_types' => 'array',
            'vehicle_types.*.id' => 'required|exists:vehicle_types,id',
            'vehicle_types.*.base_price_per_km_cents' => 'required|integer|min:0',
        ]);

        $tariff = Tariff::first() ?? new Tariff;

        $vehicleTypesData = $validated['vehicle_types'] ?? [];
        unset($validated['vehicle_types']);

        $tariff->fill($validated);
        $tariff->save();

        foreach ($vehicleTypesData as $vt) {
            VehicleType::where('id', $vt['id'])
                ->update(['base_price_per_km_cents' => $vt['base_price_per_km_cents']]);
        }

        return back()->with('success', 'Tariffs updated successfully.');
    }
}
