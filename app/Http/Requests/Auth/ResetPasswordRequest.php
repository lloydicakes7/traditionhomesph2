<?php

namespace App\Http\Requests\Auth;

use App\Rules\PasswordPolicy;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The form an emailed reset link lands on.
 */
class ResetPasswordRequest extends FormRequest
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
