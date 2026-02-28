<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('items') && is_array($this->items)) {
            $items = $this->items;
            foreach ($items as &$item) {
                if (empty($item['cbm']) && ! empty($item['length_cm']) && ! empty($item['width_cm']) && ! empty($item['height_cm'])) {
                    $item['cbm'] = round(($item['length_cm'] * $item['width_cm'] * $item['height_cm']) / 1000000, 4);
                }
            }
            $this->merge(['items' => $items]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'truck_id' => ['nullable', 'exists:trucks,id'],
            'items' => [
                'required',
                'array',
                'min:1',
                new \App\Rules\MaxPayloadVolumeRule($this->input('truck_id')),
            ],
            'items.*.name' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.weight_kg' => ['required', 'numeric', 'min:0.01'],
            'items.*.declared_value_cents' => ['nullable', 'integer', 'min:0'],
            'items.*.cbm' => ['nullable', 'numeric', 'min:0'],
            'items.*.length_cm' => ['nullable', 'integer', 'min:0'],
            'items.*.width_cm' => ['nullable', 'integer', 'min:0'],
            'items.*.height_cm' => ['nullable', 'integer', 'min:0'],
            'items.*.is_dangerous' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        if (auth()->user()->hasRole('admin')) {
            $rules['company_id'] = ['required', 'exists:companies,id'];
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'items.required' => 'At least one item must be added to the order.',
            'items.*.name.required' => 'The item name is required.',
            'items.*.quantity.min' => 'Quantity must be at least 1.',
            'items.*.weight_kg.min' => 'Weight must be greater than 0.',
        ];
    }
}
