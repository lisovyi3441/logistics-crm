<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TruckRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $truckId = $this->route('truck') ? $this->route('truck')->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'license_plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('trucks', 'license_plate')->ignore($truckId),
                'regex:/^[ABCEHIKMOPTX]{2}\s?\d{4}\s?[ABCEHIKMOPTX]{2}$/ui',
            ],
            'vehicle_type_id' => ['required', 'exists:vehicle_types,id'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'license_plate.regex' => 'Invalid license plate format. Example: AA 1234 BB.',
        ];
    }
}
