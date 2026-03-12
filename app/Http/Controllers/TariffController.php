<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\UpdateTariffRequest;
use App\Models\Tariff;
use App\Models\VehicleType;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TariffController extends Controller
{
    /**
     * Show the form for editing tariffs.
     */
    public function edit(): Response
    {
        $this->authorize('update', Tariff::class);

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

    /**
     * Update tariffs in storage.
     */
    public function update(UpdateTariffRequest $request): RedirectResponse
    {
        $this->authorize('update', Tariff::class);

        $validated = $request->validated();
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
