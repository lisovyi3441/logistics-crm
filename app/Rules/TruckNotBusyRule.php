<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TruckNotBusyRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $busyOrder = \App\Models\Order::where('truck_id', $value)
            ->where('status', \App\Enums\OrderStatus::IN_TRANSIT->value)
            ->first();

        if ($busyOrder) {
            $fail("The selected truck is currently busy executing order #{$busyOrder->order_number}.");
        }
    }
}
