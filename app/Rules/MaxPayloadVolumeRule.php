<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Truck;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class MaxPayloadVolumeRule implements ValidationRule
{
    public function __construct(protected ?int $truckId) {}

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->truckId || ! is_array($value)) {
            return;
        }

        $truck = Truck::find($this->truckId);

        if (! $truck) {
            return;
        }

        $totalWeight = 0;
        $totalCbm = 0;

        foreach ($value as $item) {
            $totalWeight += (float) ($item['weight_kg'] ?? 0);
            $totalCbm += (float) ($item['cbm'] ?? 0);
        }

        if ($totalWeight > $truck->max_weight_kg) {
            $fail("The total payload weight ({$totalWeight}kg) exceeds the maximum capacity of the selected truck ({$truck->max_weight_kg}kg).");
        }

        if ($totalCbm > $truck->max_volume_cbm) {
            $fail("The total volume ({$totalCbm} cbm) exceeds the maximum capacity of the selected truck ({$truck->max_volume_cbm} cbm).");
        }
    }
}
