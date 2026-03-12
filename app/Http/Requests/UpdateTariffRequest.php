<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTariffRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(Permissions::EDIT_TARIFFS->value);
    }

    public function rules(): array
    {
        return [
            'insurance_rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'tax_rate_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'adr_surcharge_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'vehicle_types' => ['array'],
            'vehicle_types.*.id' => ['required', 'exists:vehicle_types,id'],
            'vehicle_types.*.base_price_per_km_cents' => ['required', 'integer', 'min:0'],
        ];
    }
}
