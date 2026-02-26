<?php

namespace App\Http\Requests;

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends FormRequest
{
    use ProfileValidationRules;

    public function authorize(): bool
    {
        return auth()->user() && auth()->user()->hasRole('admin');
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $userId = $user instanceof User ? $user->id : $user;

        return [
            'name' => ['required', 'string', 'min:2', 'max:50', 'regex:/^[\pL\s\-\.]+$/u'],
            'email' => $this->emailRules($userId),
            'password' => ['nullable', 'string', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'company_id' => ['required_if:role,manager,customer', 'nullable', 'exists:companies,id'],
            'role' => ['required', 'string', 'exists:roles,name'],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.regex' => 'The name may only contain letters, spaces, dashes, and dots.',
            'company_id.required_if' => 'The company field is required when the role is manager or customer.',
        ];
    }
}
