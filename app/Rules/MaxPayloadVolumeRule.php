<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\VehicleType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxPayloadVolumeRule implements ValidationRule
{
    public function __construct(protected ?int $vehicleTypeId) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->vehicleTypeId || ! is_array($value)) {
            return;
        }

        $vehicleType = VehicleType::find($this->vehicleTypeId);

        if (! $vehicleType) {
            return;
        }

        $totalWeight = 0;
        $totalCbm = 0;

        foreach ($value as $item) {
            $totalWeight += (float) ($item['weight_kg'] ?? 0);
            $totalCbm += (float) ($item['cbm'] ?? 0);
        }

        if ($totalWeight > $vehicleType->max_weight_kg) {
            $fail("The total payload weight ({$totalWeight}kg) exceeds the maximum capacity of the selected vehicle type ({$vehicleType->max_weight_kg}kg).");
        }

        $maxVol = (float) $vehicleType->max_volume_cbm;
        if ($maxVol > 0 && $totalCbm > $maxVol) {
            $fail("The total volume ({$totalCbm} cbm) exceeds the maximum capacity of the selected vehicle type ({$maxVol} cbm).");
        }
    }
}
