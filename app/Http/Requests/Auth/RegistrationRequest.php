<?php

namespace App\Http\Requests\Auth;

use App\Enums\CivilStatus;
use App\Enums\Sex;
use App\Rules\EmailAvailable;
use App\Rules\PasswordPolicy;
use App\Rules\PhilippineMobileNumber;
use App\Rules\RegistrationPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * The three-step registration wizard's rules.
 *
 * The applicant gives the little the office needs to open an account: who they
 * are and how to reach them, the address they sign in with, and their consent.
 * Everything else is asked for once the account exists, on the onboarding form.
 */
class RegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    // ===== Rules =====

    /**
     * Every field of the three steps, keyed by the wizard's own properties.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'form.surname'      => ['required', 'string', 'max:255', 'regex:/^[^\d]+$/u'],
            'form.first_name'   => ['required', 'string', 'max:255', 'regex:/^[^\d]+$/u'],
            'form.middle_name'  => ['nullable', 'string', 'max:255', 'regex:/^[^\d]*$/u'],
            'form.suffix'       => ['nullable', 'string', 'max:50', 'regex:/^[^\d]*$/u'],
            'form.mobile_no'    => ['required', 'string', 'max:11', new PhilippineMobileNumber],
            'form.birth_date'   => ['required', 'date', 'before:today', 'after:1900-01-01'],
            'form.sex'          => ['required', Rule::in(Sex::values())],
            'form.civil_status' => ['required', Rule::in(CivilStatus::values())],

            'email'    => ['required', 'string', 'email', 'max:255', new EmailAvailable],
            'password' => ['required', 'string', 'confirmed', new RegistrationPassword, new PasswordPolicy],

            'consent.terms'   => ['accepted'],
            'consent.privacy' => ['accepted'],
        ];
    }
}
