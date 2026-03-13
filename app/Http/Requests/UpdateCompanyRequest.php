<?php

namespace App\Http\Requests;

use App\Enums\Permissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCompanyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        $company = $this->route('company');
        $companyId = is_object($company) ? $company->id : $company;

        return $user && (
            $user->can(Permissions::EDIT_COMPANIES->value) ||
            (int) $user->company_id === (int) $companyId
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $company = $this->route('company');
        $companyId = is_object($company) ? $company->id : $company;

        return [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'vat_number' => ['nullable', 'string', 'regex:/^[A-Z0-9]{5,15}$/', 'unique:companies,vat_number,'.$companyId],
            'address' => ['nullable', 'string', 'min:4', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'regex:/^\+?[0-9\s\-\(\)]{10,20}$/'],
            'contact_email' => ['nullable', 'email:rfc,dns', 'max:255'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'vat_number.regex' => 'The VAT number must contain only uppercase letters and numbers (e.g. PL1234567890).',
            'contact_phone.regex' => 'The contact phone contains invalid characters. Allowed: numbers, spaces, +, -, (, )',
        ];
    }
}
