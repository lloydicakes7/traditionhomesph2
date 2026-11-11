<?php

namespace App\Http\Requests\Auth;

use App\Rules\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The password an office-issued set-password link asks the homeowner for.
 */
class SetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'string', 'email', 'max:255'],
            'password'              => ['required', 'string', 'confirmed', new PasswordPolicy],
            'password_confirmation' => ['required', 'string'],
        ];
    }
}
